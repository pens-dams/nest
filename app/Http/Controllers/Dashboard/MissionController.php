<?php

namespace App\Http\Controllers\Dashboard;

use App\Events\Flight\FlightUpdatedOrCreated;
use App\Http\Controllers\Controller;
use App\Models\Drone;
use App\Models\Flight;
use Dentro\Yalr\Attributes\Get;
use Dentro\Yalr\Attributes\Post;
use Dentro\Yalr\Attributes\Put;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use MatanYadaev\EloquentSpatial\Objects\Point;

class MissionController extends Controller
{
    #[Get('mission', name: 'dashboard.mission')]
    public function index(): Response
    {
        $drones = Drone::query()
            ->where('team_id', $this->getAuthenticatedUser()->currentTeam?->id)
            ->get();

        return Inertia::render('Dashboard/Mission/Index', compact('drones'));
    }

    #[Get('mission/{drone}', name: 'dashboard.mission.show')]
    public function show(Drone $drone): Response
    {
        $title = 'Manage Mission for '.$drone->name;

        $flights = $drone->flights()->with(['paths', 'logs', 'intersections.paths'])->get();

        return Inertia::render(
            'Dashboard/Mission/Show',
            compact('drone', 'title', 'flights'),
        );
    }

    #[Post('mission/{drone}', name: 'dashboard.mission.store')]
    public function store(Request $request, Drone $drone): RedirectResponse
    {
        $data = $request->validate([
            'code' => 'nullable|string',
            'departure' => 'required|date',
            'points' => 'required|array|min:2',
            'points.*.lat' => 'required|numeric',
            'points.*.lng' => 'required|numeric',
            'points.*.alt' => 'required|numeric',
        ]);

        $flight = new Flight();

        $firstPoint = $data['points'][0];
        $lastPoint = $data['points'][count($data['points']) - 1];

        $flight->drone()->associate($drone);
        $flight->code = $data['code'] ?? Str::random(5);
        $flight->departure = Carbon::parse($data['departure']);
        $flight->from = new Point($firstPoint['lat'], $firstPoint['lng']);
        $flight->to = new Point($lastPoint['lat'], $lastPoint['lng']);

        $flight->save();

        $this->savePoints($flight, $data['points']);

        Event::dispatch(new FlightUpdatedOrCreated($flight));

        return redirect()
            ->route('dashboard.mission.show', $drone)
            ->with('success', 'Flight planning created successfully');
    }

    #[Put('mission/{flight}', name: 'dashboard.mission.update')]
    public function update(Request $request, Flight $flight): RedirectResponse
    {
        $data = $request->validate([
            'code' => 'nullable|string',
            'departure' => 'required|date',
            'points' => 'required|array|min:2',
            'points.*.lat' => 'required|numeric',
            'points.*.lng' => 'required|numeric',
            'points.*.alt' => 'required|numeric',
        ]);


        $firstPoint = $data['points'][0];
        $lastPoint = $data['points'][count($data['points']) - 1];

        $flight->code = $data['code'] ?? Str::random(5);
        $flight->departure = Carbon::parse($data['departure']);
        $flight->from = new Point($firstPoint['lat'], $firstPoint['lng']);
        $flight->to = new Point($lastPoint['lat'], $lastPoint['lng']);

        $flight->paths()->delete();
        $flight->logs()->delete();

        $flight->save();

        $this->savePoints($flight, $data['points']);

        Event::dispatch(new FlightUpdatedOrCreated($flight));

        return redirect()
            ->route('dashboard.mission.show', $flight->drone)
            ->with('success', 'Flight planning created successfully');
    }

    /**
     * @param Flight $flight
     * @param array<int, array{lat: float, lng: float, alt: float}> $points
     * @return void
     */
    protected function savePoints(Flight $flight, array $points): void
    {
        $sequence = 0;

        foreach ($points as $point) {
            $path = new Flight\Path();
            $path->flight()->associate($flight);

            $path->position = new Point($point['lat'], $point['lng']);
            $path->altitude = $point['alt'];
            $path->sequence = $sequence++;
            $path->meta = [
                'lat' => $point['lat'],
                'lng' => $point['lng'],
                'alt' => $point['alt'],
            ];

            $path->save();
        }
    }
}
