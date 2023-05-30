<?php

namespace Database\Seeders;

use App\Events\Flight\FlightCreated;
use App\Jobs\Flight\CalculateAllCollision;
use App\Models\Computer;
use App\Models\Drone;
use App\Models\Flight;
use App\Models\User;
use Database\Factories\DroneFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use MatanYadaev\EloquentSpatial\Objects\Point;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory(10)->withPersonalTeam()->create();

        /** @var User $user */
        $user = User::factory()->withPersonalTeam()->create([
            'name' => 'Test User',
            'email' => 'test@nest.roisc.pens.ac.id',
        ]);

        Computer::factory()
            ->count(3)
            ->afterCreating(fn(Computer $computer) => Drone::factory()
                ->for($computer, 'compute')
                ->for($user->currentTeam()->firstOrFail(), 'team')
                ->count(5)
//                ->afterCreating(function (Drone $drone) {
//                    if (app()->environment('testing')) {
//                        return;
//                    }
//
//                    $knownLocations = collect(DroneFactory::$knownLocations)
//                        ->reject(function ($location) use ($drone) {
//                            return $location['lat'] === $drone->standby_location->latitude
//                                && $location['lng'] === $drone->standby_location->longitude;
//                        });
//
//                    $randomLocation = $knownLocations->random();
//
//                    Flight::factory()->create([
//                        'drone_id' => $drone->id,
//                        'from' => new Point(
//                            $drone->standby_location->latitude,
//                            $drone->standby_location->longitude,
//                        ),
//                        'to' => new Point(
//                            $randomLocation['lat'],
//                            $randomLocation['lng'],
//                        ),
//                    ]);
//                })
                ->create()
            )
            ->create();

        $this->call(FlightTestSeeder::class);

        if (!app()->environment('testing')) {
            foreach (Flight::query()->cursor() as $flight) {
                // @phpstan-ignore-next-line
                Event::dispatch(new FlightCreated($flight));
            }
        }
    }
}
