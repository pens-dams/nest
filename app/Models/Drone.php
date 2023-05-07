<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MatanYadaev\EloquentSpatial\Objects\Point;

/**
 * Class Drone
 *
 * @property  string  $name
 * @property  string  $description
 * @property  Point  $standby_location
 * @property  Collection<int, Flight> $flights
 */
class Drone extends Model
{
    use HasFactory;

    protected $table = 'drones';

    protected $casts = [
        'standby_location' => Point::class,
    ];

    /**
     * @return BelongsTo<Team, Drone>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }

    /**
     * @return BelongsTo<Computer, Drone>
     */
    public function compute(): BelongsTo
    {
        return $this->belongsTo(Computer::class, 'compute_id', 'id');
    }

    /**
     * @return HasMany<Flight>
     */
    public function flights(): HasMany
    {
        return $this->hasMany(Flight::class, 'drone_id', 'id');
    }
}
