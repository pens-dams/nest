<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Drone;
use Dentro\Yalr\Attributes\Get;
use Inertia\Inertia;
use Inertia\Response;

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

        return Inertia::render('Dashboard/Mission/Show', compact('drone', 'title'));
    }
}
