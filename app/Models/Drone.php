<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Drone extends Model
{
    use HasFactory;

    protected $table = 'drones';

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }

    public function compute(): BelongsTo
    {
        return $this->belongsTo(Computer::class, 'compute_id', 'id');
    }
}
