<?php

namespace Database\Seeders;

use App\Events\Flight\FlightUpdatedOrCreated;
use App\Models\Computer;
use App\Models\Drone;
use App\Models\Flight;
use App\Models\User;
use App\Objects\ObjectMovementMocker;
use Database\Factories\DroneFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use MatanYadaev\EloquentSpatial\Objects\Point;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;
    use WithFaker;

    public function run(): void
    {
        $this->setUpFaker();

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
                ->afterCreating(function (Drone $drone) {
                    if (app()->environment('testing')) {
                        return;
                    }

                    $knownLocations = collect(DroneFactory::knownLocationsRandomizer())
                        ->reject(function ($location) use ($drone) {
                            return $location['lat'] === $drone->standby_location->latitude
                                && $location['lng'] === $drone->standby_location->longitude;
                        });

                    $randomLocation = $knownLocations->random();

                    Flight::factory()
                        ->afterCreating(function (Flight $flight) {
                            $sequence = 1;

                            $createPath = function (Point $position) use ($flight, &$sequence): void {
                                $path = new Flight\Path();
                                $path->flight()->associate($flight);

                                $path->position = $position;
                                $path->altitude = $this->faker()->numberBetween(50, 60);
                                $path->sequence = $sequence++;
                                $path->meta = [
                                    'lat' => $path->position->latitude,
                                    'lng' => $path->position->longitude,
                                    'alt' => $path->altitude,
                                ];

                                $path->save();
                            };

                            $createPath($flight->from);
                            $this->randomMovement($flight, $sequence);
                            /** @var Flight\Path $lastPath */
                            $lastPath = $flight->paths()->latest()->firstOrFail();
                            $flight->to = new Point(
                                $lastPath->position->latitude,
                                $lastPath->position->longitude,
                            );
                        })
                        ->create([
                            'drone_id' => $drone->id,
                            'from' => new Point(
                                $drone->standby_location->latitude,
                                $drone->standby_location->longitude,
                            ),
                            'to' => new Point(
                                $randomLocation['lat'],
                                $randomLocation['lng'],
                            ),
                        ]);
                })
                ->create()
            )
            ->create();

        if (!app()->environment('testing')) {
            foreach (Flight::query()->cursor() as $flight) {
                // @phpstan-ignore-next-line
                Event::dispatch(new FlightUpdatedOrCreated($flight));
            }
        }
    }

    private function randomMovement(Flight $flight, int $sequence): void
    {
        $locationMock = new ObjectMovementMocker(
            latitude: $flight->drone->standby_location->latitude,
            longitude: $flight->drone->standby_location->longitude,
            altitude: $flight->planned_altitude,
        );

        $degreeRandomizer = function (): int {
            $left = $this->faker()->numberBetween(50, 120);
            $right = $this->faker()->numberBetween(240, 300);

            return $this->faker()->randomElement([$left, $right]);
        };

        foreach (range(1, $this->faker()->numberBetween(2, 10)) as $step) {
            $locationMock->turn($degreeRandomizer());

            $locationMock->advance(
                $this->faker()->numberBetween(50, 300),
            );

            $path = new Flight\Path();
            $path->flight()->associate($flight);

            $path->position = new Point(
                $locationMock->getLatitude(),
                $locationMock->getLongitude(),
            );
            $path->altitude = $this->faker()->numberBetween(50, 60);
            $path->sequence = $sequence++;
            $path->meta = [
                'lat' => $path->position->latitude,
                'lng' => $path->position->longitude,
                'alt' => $path->altitude,
            ];

            $path->save();
        }
    }
}
