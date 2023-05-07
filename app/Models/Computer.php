<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticable;
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
        'latest_handshake' => 'datetime',
    ];

    /**
     * @return BelongsTo<Team, Computer>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }

    /**
     * @return HasMany<Drone>
     */
    public function drones(): HasMany
    {
        return $this->hasMany(Drone::class, 'compute_id', 'id');
    }

    /**
     * @param $query
     * @return SpatialBuilder<Computer>
     */
    public function newEloquentBuilder($query): SpatialBuilder
    {
        // @phpstan-ignore-next-line
        return new SpatialBuilder($query);
    }

    /**
     * @return SpatialBuilder<Computer>
     */
    public static function query(): SpatialBuilder
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return parent::query();
    }
}
