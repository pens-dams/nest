<?php

namespace App\Models;

use App\Models\Flight\Log;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
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
 * @property  Drone $drone
 * @property  int  $drone_id
 * @property  Carbon $departure
 * @property  int  $speed
 * @property  string  $name
 * @property  string  $color
 * @property  array  $meta
 * @property  \Illuminate\Support\Carbon  $created_at
 * @property  \Illuminate\Support\Carbon  $updated_at
 * @property string $ulid
 * @property Collection<int, Flight\Path> $paths
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
     * @return Relations\HasMany<Flight\Path>
     */
    public function paths(): Relations\HasMany
    {
        return $this->hasMany(Flight\Path::class, 'flight_id')->orderBy('sequence');
    }

    /**
     * @return Relations\HasMany<Log>
     */
    public function logs(): Relations\HasMany
    {
        return $this->hasMany(Flight\Log::class, 'flight_id');
    }

    /**
     * @return Relations\MorphToMany<Flight\Intersect>
     */
    public function intersections(): Relations\MorphToMany
    {
        return $this->morphToMany(
            Flight\Intersect::class,
            'intersectable',
            'flight_intersectable',
            'intersectable_id',
            'intersect_ulid',
        );
    }

    protected static function boot(): void
    {
        parent::boot();
    }
}
