<?php

namespace App\Models;

use App\Events\Flight\FlightCreated;
use App\Models\Flight\Log;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
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
 * @property  Carbon $departure
 * @property  int  $speed
 * @property  string  $name
 * @property  array  $meta
 * @property  \Illuminate\Support\Carbon  $created_at
 * @property  \Illuminate\Support\Carbon  $updated_at
 * @property string $ulid
 */
class Flight extends Model
{
    use HasFactory;
    use HasUlids;

    protected $table = 'flights';

    protected $primaryKey = 'ulid';

    protected $keyType = 'string';

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

    /**
     * @return Relations\HasMany<Log>
     */
    public function logs(): Relations\HasMany
    {
        return $this->hasMany(Flight\Log::class, 'flight_id');
    }

    protected static function boot(): void
    {
        parent::boot();
    }
}
