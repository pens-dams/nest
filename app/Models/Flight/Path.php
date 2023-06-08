<?php

namespace App\Models\Flight;

use App\Models\Flight;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Support\Carbon;
use MatanYadaev\EloquentSpatial\Objects\Point;

/**
 * Class Path
 *
 * @property  int $sequence
 * @property  Point $position
 * @property  float $altitude
 * @property  float $speed
 * @property  array $meta
 * @property  Carbon $created_at
 * @property  Carbon $updated_at
 * @property string $ulid
 */
class Path extends Model
{
    use HasFactory;
    use HasUlids;

    protected $table = 'flight_paths';

    protected $primaryKey = 'ulid';

    protected $casts = [
        'position' => Point::class,
        'meta' => 'array',
        'altitude' => 'float',
        'speed' => 'float',
        'sequence' => 'int',
    ];

    /**
     * @return Relations\BelongsTo<Flight, Path>
     */
    public function flight(): Relations\BelongsTo
    {
        return $this->belongsTo(Flight::class, 'flight_id', 'ulid');
    }
}
