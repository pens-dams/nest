<?php

namespace App\Actions\Flight\Path;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CheckPathsCollisionFromRequest
{
    public function __construct(
        protected Request $request,
    )
    {
    }

    public function check(): Collection
    {
        $this->request->validate([
            'from' => 'required|array',
            'from.lat' => 'required|numeric',
            'from.lng' => 'required|numeric',
            'from.alt' => 'required|numeric',
            'to' => 'required|array',
            'to.lat' => 'required|numeric',
            'to.lng' => 'required|numeric',
            'to.alt' => 'required|numeric',
        ]);
    }
}
