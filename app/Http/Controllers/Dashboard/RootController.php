<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Dentro\Yalr\Attributes\Get;
use Inertia\Inertia;
use Inertia\Response;

class RootController extends Controller
{
    #[Get('/', name: 'dashboard.root')]
    public function index(): Response
    {
        return Inertia::render('Dashboard');
    }
}
