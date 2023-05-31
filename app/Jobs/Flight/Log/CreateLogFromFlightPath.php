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

class CreateLogFromFlightPath implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Flight $flight,
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->fillMetaPathData();

        $this->flight = $this->flight->refresh();

        $paths = $this->flight->paths;

        $flightDeparture = $this->flight->departure;
        $flightSpeed = $this->flight->speed;
        $flightMeta = $this->flight->meta;

        $flightMeta['distance'] = [
            'value' => $paths->sum('meta.distance.value'),
            'unit' => 'meter',
        ];

        $flightMeta['time'] = [
            'value' => $paths->sum('meta.time.value'),
            'unit' => 'second',
        ];

        $this->flight->meta = $flightMeta;
        $this->flight->save();

        $trueSecond = 0;
        $trueTime = $flightMeta['time']['value'];

        $origin = $paths->firstOrFail();

        foreach ($paths as $path) {
            $destination = $path;

            if ($origin->ulid === $destination->ulid) {
                continue;
            }

            $time = $destination->meta['time']['value'] - $origin->meta['time']['value'];

            foreach (range(0, $time) as $second) {
                $trueSecond++;
                $trueProgress = $trueSecond / $trueTime * 100;

                $progress = $second / $time * 100;

                // calculate current latitude, longitude and altitude based on progress
                $currentLat = $origin->position->latitude + ($destination->position->latitude - $origin->position->latitude) * $progress / 100;
                $currentLng = $origin->position->longitude + ($destination->position->longitude - $origin->position->longitude) * $progress / 100;
                $currentAlt = $origin->altitude + ($destination->altitude - $origin->altitude) * $progress / 100;

                $currentPoint = new Point($currentLat, $currentLng);

                $coordinate = Geo::latLngToVector3Relative(
                    point: [
                        'lat' => $currentLat,
                        'lng' => $currentLng,
                        'alt' => $currentAlt,
                    ],
                );

                $this->flight->logs()->create([
                    'position' => $currentPoint,
                    'speed' => $flightSpeed,
                    'altitude' => $currentAlt,
                    'datetime' => $flightDeparture->clone()->addSeconds($trueSecond),
                    'meta' => [
                        'lla' => [
                            'lat' => $currentLat,
                            'lng' => $currentLng,
                            'alt' => $currentAlt,
                        ],
                        'progress' => $trueProgress,
                        'coordinate' => $coordinate,
                        'anchor' => config('nest.anchor'),
                    ],
                ]);
            }
        }
    }

    private function fillMetaPathData(): void
    {
        $speed = $this->flight->speed;
        $paths = $this->flight->paths;

        $origin = $paths->firstOrFail();

        foreach ($paths as $path) {
            $destination = $path;

            if ($origin->ulid === $destination->ulid) {
                $meta = $path->meta;

                $meta['distance'] = [
                    'value' => 0,
                    'unit' => 'meter',
                ];

                $meta['time'] = [
                    'value' => 0,
                    'unit' => 'second',
                ];

                $path->meta = $meta;
                $path->save();

                continue;
            }

            $meta = $path->meta;

            // get the distance between the origin and destination
            $distance = Geo::getDistanceInMeter($origin->position, $destination->position);

            // get the time in seconds
            $time = $distance / ($speed * 1000 / 3600);

            $meta['distance'] = [
                'value' => $distance,
                'unit' => 'meter',
            ];

            $meta['time'] = [
                'value' => $time,
                'unit' => 'second',
            ];

            $path->meta = $meta;

            $path->save();
        }
    }
}
