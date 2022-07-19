<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Dentro\Yalr\Attributes\Get;
use Inertia\Inertia;
use Inertia\Response;

class MissionController extends Controller
{
    #[Get('mission', name: 'dashboard.mission')]
    public function index(): Response
    {
        return Inertia::render('Dashboard/Mission/Index');
    }
}
