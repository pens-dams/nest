<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Illuminate\Database\Eloquent\Relations;

/**
 * Class Flight
 *
 * @property  string $code
 * @property  Point  $from
 * @property  Point  $to
 * @property  int  $planned_altitude
 * @property  \App\Models\Drone  $drone
 * @property  int  $drone_id
 * @property  int  $id
 * @property  Carbon $departure
 * @property  int  $speed
 * @property  string  $name
 * @property  array  $meta
 * @property  \Illuminate\Support\Carbon  $created_at
 * @property  \Illuminate\Support\Carbon  $updated_at
 */
class Flight extends Model
{
    use HasFactory;

    protected $table = 'flights';

    protected $casts = [
        'from' => Point::class,
        'to' => Point::class,
        'meta' => 'array',
        'departure' => 'datetime',
    ];

    /**
     * @return Relations\BelongsTo<Drone, Flight>
     */
    public function drone(): Relations\BelongsTo
    {
        return $this->belongsTo(Drone::class);
    }
}
