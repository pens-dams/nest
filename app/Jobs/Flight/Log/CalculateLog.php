<?php

namespace App\Jobs\Flight\Log;

use App\Models\Flight;
use App\Supports\Geo;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MatanYadaev\EloquentSpatial\Objects\Point;

class CalculateLog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Flight $flight,
        public float $second,
        public float $time,
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $origin = $this->flight->from;

        $destination = $this->flight->to;

        // speed in km/h
        $speed = $this->flight->speed;

        $progress = $this->second / $this->time * 100;

        // calculate current latitude and longitude based on progress
        $currentLat = $origin->latitude + ($destination->latitude - $origin->latitude) * $progress / 100;

        $currentLng = $origin->longitude + ($destination->longitude - $origin->longitude) * $progress / 100;

        $currentPoint = new Point($currentLat, $currentLng);

        // calculate ECEF coordinates from current latitude and longitude
        $coordinate = Geo::latLngToVector3Relative(
            point: [
                'lat' => $currentLat,
                'lng' => $currentLng,
                'alt' => $this->flight->planned_altitude,
            ],
            referencePoint: [
                'lat' => config('nest.anchor.lat'),
                'lng' => config('nest.anchor.lng'),
                'alt' => config('nest.anchor.alt'),
            ],
        );

        $this->flight->logs()->create([
            'position' => $currentPoint,
            'speed' => $speed,
            'altitude' => $this->flight->planned_altitude,
            'datetime' => $this->flight->departure->clone()->addSeconds((int)$this->second),
            'meta' => [
                'progress' => $progress,
                'coordinate' => $coordinate,
                'anchor' => config('nest.anchor'),
            ],
        ]);
    }
}
