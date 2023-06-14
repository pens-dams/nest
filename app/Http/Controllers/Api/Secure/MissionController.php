<?php

namespace App\Http\Controllers\Api\Secure;

use App\Actions\Flight\Path\CheckPathsCollisionFromRequest;
use App\Http\Controllers\Controller;
use Dentro\Yalr\Attributes\Get;
use Dentro\Yalr\Attributes\Prefix;
use Illuminate\Http\JsonResponse;

#[Prefix('mission')]
class MissionController extends Controller
{
    #[Get('path/check', name: 'api.secure.mission.check')]
    public function pathCheck(CheckPathsCollisionFromRequest $action): JsonResponse
    {
        return response()->json([
            'data' => $action->check(),
        ]);
    }
}
