<?php

use App\Http\Controllers\Dashboard as DashboardController;

return [

    /*
    |--------------------------------------------------------------------------
    | Preloads
    |--------------------------------------------------------------------------
    | String of class name that instance of \Dentro\Yalr\Contracts\Bindable
    | Preloads will always been called even when laravel routes has been cached.
    | It is the best place to put Rate Limiter and route binding related code.
    */

    'preloads' => [
        App\Http\Routes\RateLimiter::class,
        App\Http\Routes\RouteModelBinding::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Router group settings
    |--------------------------------------------------------------------------
    | Groups are used to organize and group your routes. Basically the same
    | group that used in common laravel route.
    |
    | 'group_name' => [
    |     // laravel group route options can contains 'middleware', 'prefix',
    |     // 'as', 'domain', 'namespace', 'where'
    | ]
    */

    'groups' => [
        'web' => [
            'middleware' => 'web',
            'prefix' => '',
        ],
        'api' => [
            'middleware' => 'api',
            'prefix' => 'api',
        ],
        'dashboard' => [
            'middleware' => [
                'web',
                'auth:sanctum',
                config('jetstream.auth_session'),
                'verified',
            ],
            'prefix' => 'dashboard',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    | Below is where our route is loaded, it read `groups` section above.
    | keys in this array are the name of route group and values are string
    | class name either instance of \Dentro\Yalr\Contracts\Bindable or
    | controller that use attribute that inherit \Dentro\Yalr\RouteAttribute
    */

    'web' => [
        App\Http\Controllers\RootController::class,
    /** @inject web **/
    ],
    'api' => [
        App\Http\Controllers\Api\UserController::class,
    /** @inject api **/
    ],
    'dashboard' => [
        DashboardController\RootController::class,
        DashboardController\ComputeController::class,
        DashboardController\MissionController::class,
        DashboardController\MonitorController::class,
    /** @inject dashboard **/
    ],
];
