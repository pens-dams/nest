<?php

namespace App\Listeners\Flight\FlightCreated;

use App\Events\Flight\FlightCreated;
use App\Events\Flight\Log\LogSeriesCreated;
use App\Jobs\Flight\Log\CalculateLog;
use App\Jobs\Flight\Log\CreateLogFromFlightPath;
use App\Supports\Geo;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;

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
     * @throws \Throwable
     */
    public function handle(FlightCreated $event): void
    {
        $flight = $event->flight;
        $batch = Bus::batch([])->name('Create flight log: ' . $flight->ulid);

        if ($flight->paths()->count() > 2) {
            $batch->add(new CreateLogFromFlightPath($flight));

            $batch->dispatch();

            return;
        }

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

        foreach (range(0, $time) as $second) {
            $batch->add(new CalculateLog($flight, $second, $time));
        }

        $batch->then(function () use ($flight) {
            Event::dispatch(new LogSeriesCreated($flight));
        });

        $batch->dispatch();
    }
}
