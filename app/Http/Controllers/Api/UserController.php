<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Dentro\Yalr\Attributes\Get;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    #[Get('/user', middleware: 'auth:sanctum')]
    public function index(Request $request): JsonResponse
    {
        return $this->basicJsonResponse($request->user());
    }
}
