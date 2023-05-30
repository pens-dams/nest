<?php

namespace Tests\Feature;

use App\Jobs\Flight\CalculateAllCollision;
use Database\Seeders\FlightTestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class FlightTest extends TestCase
{
    use RefreshDatabase;

    public function test_euclidean_distance_calculation(): void
    {
//        $this->seed(FlightTestSeeder::class);

        $batch = Bus::batch([
            new CalculateAllCollision(),
        ])->dispatch();

        $this->assertTrue(true);
        $this->assertDatabaseCount('flight_intersects', 3);
    }
}
