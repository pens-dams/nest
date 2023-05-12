<?php

namespace App\Models\Flight;

use App\Models\Flight;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MatanYadaev\EloquentSpatial\Objects\Point;

class Log extends Model
{
    use HasFactory;
    use HasUlids;

    protected $table = 'flight_logs';

    protected $primaryKey = 'ulid';

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
        'datetime' => 'datetime',
    ];

    /**
     * @return BelongsTo<Flight, Log>
     */
    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class);
    }
}
