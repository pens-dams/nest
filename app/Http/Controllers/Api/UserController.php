<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dentro\Yalr\Attributes\Get;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    #[Get('/user', middleware: 'auth:sanctum')]
    public function index(Request $request): JsonResponse
    {
        return $this->basicJsonResponse($request->user());
    }
}
