<?php

namespace Database\Seeders;

use App\Events\Flight\FlightUpdatedOrCreated;
use App\Models\Computer;
use App\Models\Drone;
use App\Models\Flight;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Event;

class SimulationSeeder extends Seeder
{
    use Concern\CSVRestorer;

    /**
     * Run the database seeds.
     */
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
                ->create()
            )
            ->create();

        $this->restoreCsvToTable('flights', database_path('seeders/data/simulation/flights.csv'));
        $this->restoreCsvToTable('flight_paths', database_path('seeders/data/simulation/flight_paths.csv'));

        if (!app()->environment('testing')) {
            foreach (Flight::query()->cursor() as $flight) {
                // @phpstan-ignore-next-line
                Event::dispatch(new FlightUpdatedOrCreated($flight));
            }
        }
    }
}
