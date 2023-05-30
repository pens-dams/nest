<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Flight;
use Dentro\Yalr\Attributes\Get;
use Inertia\Inertia;
use Inertia\Response;

class MonitorController extends Controller
{
    #[Get('monitor', name: 'dashboard.monitor')]
    public function index(): Response
    {
        $flights = Flight::query()
            ->with('drone', 'logs')
            ->get();

        $intersections = Flight\Intersect::query()
            ->get();

        return Inertia::render('Dashboard/Monitor/Index', [
            'flights' => $flights,
            'intersections' => $intersections,
        ]);
    }
}
