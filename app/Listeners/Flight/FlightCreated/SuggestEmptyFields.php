<?php

namespace App\Listeners\Flight\FlightCreated;

use App\Events\Flight\FlightCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SuggestEmptyFields
{
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

        if ($flight->speed === null) {
            $flight->speed = 15; // km/h
        }

        if ($flight->planned_altitude === null) {
            $flight->planned_altitude = 50;
        }

        $flight->save();
    }
}
