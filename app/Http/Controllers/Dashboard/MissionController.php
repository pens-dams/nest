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
        $drones = Drone::query()->get();

        return Inertia::render('Dashboard/Mission/Index', compact('drones'));
    }

    #[Get('mission/{drone}', name: 'dashboard.mission.show')]
    public function show(Drone $drone): Response
    {
        return Inertia::render('Dashboard/Mission/Show', compact('drone'));
    }
}
