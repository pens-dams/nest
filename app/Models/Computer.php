<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\HasApiTokens;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\SpatialBuilder;

class Computer extends Authenticable
{
    use HasApiTokens;
    use HasFactory;

    protected $table = 'computers';

    protected $hidden = [
        'team_id',
    ];

    protected $casts = [
        'token' => 'encrypted',
        'position' => Point::class,
    ];

    protected $dates = [
        'latest_handshake',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }

    public function newEloquentBuilder($query): SpatialBuilder
    {
        return new SpatialBuilder($query);
    }

    public static function query(): SpatialBuilder
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return parent::query();
    }
}
