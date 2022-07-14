<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

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
}
