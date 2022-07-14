<?php

namespace App\Http\Controllers\Dashboard;

use Inertia\Inertia;
use Inertia\Response;
use Dentro\Yalr\Attributes\Get;
use App\Http\Controllers\Controller;

class RootController extends Controller
{
    #[Get('/', name: 'dashboard.root')]
    public function index(): Response
    {
        return Inertia::render('Dashboard');
    }
}
