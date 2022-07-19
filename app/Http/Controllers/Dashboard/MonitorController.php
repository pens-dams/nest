<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Dentro\Yalr\Attributes\Get;
use Inertia\Inertia;
use Inertia\Response;

class MonitorController extends Controller
{
    #[Get('monitor', name: 'dashboard.monitor')]
    public function index(): Response
    {
        return Inertia::render('Dashboard/Monitor/Index');
    }
}
