<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Computer;
use Dentro\Yalr\Attributes\Get;
use Inertia\Inertia;
use Inertia\Response;

class ComputeController extends Controller
{
    #[Get('/compute', name: 'dashboard.compute')]
    public function index(): Response
    {
        $query = Computer::query();

        return Inertia::render('Dashboard/Compute/Index', [
            'payload' => $query->paginate(),
        ]);
    }
}
