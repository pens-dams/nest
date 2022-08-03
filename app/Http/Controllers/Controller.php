<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Validation\UnauthorizedException;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    protected function basicJsonResponse(
        Arrayable|Jsonable|array|null $payload,
        $notification = '',
        $message = '',
        $status = 200,
    ): JsonResponse {
        return response()->json([
            'notification' => $notification,
            'message' => $message,
            'payload' => $payload,
        ], $status);
    }

    public function getAuthenticatedUser(): User
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            throw new UnauthorizedException();
        }

        return $user;
    }
}
