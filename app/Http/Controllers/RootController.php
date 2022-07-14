<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Dentro\Yalr\Attributes\Get;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;

class RootController extends Controller
{
    #[Get('/', name: 'root')]
    public function index(): Response
    {
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    }
}
