<?php

namespace App\Http\Routes;

use Dentro\Yalr\BaseRoute;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter as RateLimiterFacade;

class RateLimiter extends BaseRoute
{
    public function register(): void
    {
        RateLimiterFacade::for('api', static function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });

        RateLimiterFacade::for('login', static function (Request $request) {
            $email = (string) $request->input('email');

            return Limit::perMinute(5)->by($email.$request->ip());
        });

        RateLimiterFacade::for('two-factor', static function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
