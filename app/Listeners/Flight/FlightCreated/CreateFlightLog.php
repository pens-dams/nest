<?php

namespace App\Listeners\Flight\FlightCreated;

use App\Events\Flight\FlightCreated;
use App\Jobs\Flight\Log\CalculateLog;
use App\Supports\Geo;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Bus;

class CreateFlightLog implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(FlightCreated $event): void
    {
        $flight = $event->flight;

        $origin = $flight->from;

        $destination = $flight->to;

        // speed in km/h
        $speed = $flight->speed;

        // get the distance between the origin and destination
        $distance = Geo::getDistanceInMeter($origin, $destination);

        // get the time in seconds
        $time = $distance / ($speed * 1000 / 3600);

        $meta = $flight->meta;

        $meta['distance'] = [
            'value' => $distance,
            'unit' => 'meter',
        ];

        $meta['time'] = [
            'value' => $time,
            'unit' => 'second',
        ];

        $flight->meta = $meta;

        $flight->save();

        $batch = Bus::batch([])->name('Create flight log');

        foreach (range(0, $time) as $second) {
            $batch->add(new CalculateLog($flight, $second, $time));
        }

        $batch->dispatch();
    }
}
