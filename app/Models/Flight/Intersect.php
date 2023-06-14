<?php

namespace App\Models\Flight;

use App\Models\Flight;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Illuminate\Database\Eloquent\Relations;

/**
 * @property Point $intersect
 * @property float $altitude
 * @property float $radius
 * @property Carbon|null $collision_time
 * @property array|null $meta
 */
class Intersect extends Model
{
    use HasFactory;
    use HasUlids;

    protected $primaryKey = 'ulid';

    protected $keyType = 'string';

    protected $table = 'flight_intersects';

    protected $fillable = [
        'intersect',
        'radius',
        'collision_time',
    ];

    protected $casts = [
        'intersect' => Point::class,
        'altitude' => 'float',
        'collision_time' => 'datetime',
        'radius' => 'float',
        'meta' => 'array',
    ];

    /**
     * @return Relations\MorphToMany<Log>
     */
    public function logs(): Relations\MorphToMany
    {
        return $this->morphedByMany(
            Log::class,
            'intersectable',
            'flight_intersectable',
            'intersect_ulid',
            'intersectable_id',
            'ulid',
            'ulid',
        );
    }

    /**
     * @return Relations\MorphToMany<Flight>
     */
    public function flights(): Relations\MorphToMany
    {
        return $this->morphedByMany(
            Flight::class,
            'intersectable',
            'flight_intersectable',
            'intersect_ulid',
            'intersectable_id',
            'ulid',
            'ulid',
        );
    }

    /**
     * @return Relations\MorphToMany<Path>
     */
    public function paths(): Relations\MorphToMany
    {
        return $this->morphedByMany(
            Path::class,
            'intersectable',
            'flight_intersectable',
            'intersect_ulid',
            'intersectable_id',
            'ulid',
            'ulid',
        );
    }
}
