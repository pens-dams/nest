<?php

namespace App\Models\Flight;

use App\Models\Flight;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MatanYadaev\EloquentSpatial\Objects\Point;

/**
 * @property Point $position
 * @property float $altitude
 * @property float $speed
 * @property \DateTimeInterface $datetime
 * @property array $meta
 * @property string $ulid
 * @property string $flight_id
 */
class Log extends Model
{
    use HasFactory;
    use HasUlids;

    protected $table = 'flight_logs';

    protected $primaryKey = 'ulid';

    protected $keyType = 'string';

    protected $fillable = [
        'flight_id',
        'position',
        'altitude',
        'speed',
        'datetime',
        'meta',
    ];

    protected $casts = [
        'position' => Point::class,
        'meta' => 'array',
        'altitude' => 'float',
        'datetime' => 'datetime',
    ];

    /**
     * @return BelongsTo<Flight, Log>
     */
    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class, 'flight_id');
    }

    /**
     * @return BelongsTo<Path, Log>
     */
    public function path(): BelongsTo
    {
        return $this->belongsTo(Path::class, 'path_id');
    }
}
