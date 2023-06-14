<?php

namespace Tests\Feature;

use App\Events\Flight\FlightCreated;
use App\Jobs\Flight\CalculateAllCollision;
use App\Jobs\Flight\CalculateFlightCollision;
use App\Models\Flight;
use Database\Seeders\FlightTestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class FlightTest extends TestCase
{
    use RefreshDatabase;

    public function test_euclidean_distance_calculation(): void
    {
        Bus::batch([
            new CalculateAllCollision(),
        ])->dispatch();

        $this->assertTrue(true);
        $this->assertDatabaseCount('flight_intersects', 3);
    }

    public function test_log_can_be_generated_with_flight_paths(): void
    {
        /** @var Flight $flight */
        $flight = Flight::query()->whereHas('paths', null, '>', 2)->firstOrFail();

        Event::dispatch(new FlightCreated($flight));

        $this->assertGreaterThan(1, $flight->logs()->count());
    }

    public function test_single_flight_intersect_detector(): void
    {
        $flights = Flight::all();

        Bus::batch(
            $flights->map(fn(Flight $flight) => new CalculateFlightCollision($flight))->toArray()
        )->dispatch();

        $this->assertTrue(true);
        $this->assertDatabaseCount('flight_intersects', 3);
    }
}
