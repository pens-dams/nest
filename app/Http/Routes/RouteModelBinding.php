<?php

namespace App\Http\Routes;

use Illuminate\Routing\Router;
use Dentro\Yalr\Contracts\Bindable;

class RouteModelBinding implements Bindable
{
    public function __construct(
        protected Router $router
    ) {
    }

    public function bind(): void
    {
    }
}
