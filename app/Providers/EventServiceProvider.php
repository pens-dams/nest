<?php

namespace App\Providers;

use App\Events\Flight\FlightCreated;
use App\Events\Flight\Log\LogSeriesCreated;
use App\Jobs\Flight\CalculateFlightCollision;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use App\Listeners\Flight\FlightCreated as FlightCreatedListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        FlightCreated::class => [
            FlightCreatedListener\SuggestEmptyFields::class,
            FlightCreatedListener\CreateFlightLog::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot(): void
    {
        Event::listen(LogSeriesCreated::class, function (LogSeriesCreated $event) {
            Bus::batch([new CalculateFlightCollision($event->flight)])
                ->name('Calculate flight collision for flight: ' . $event->flight->ulid)
                ->dispatch();
        });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
