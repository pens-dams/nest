<?php

namespace Database\Seeders;

use App\Events\Flight\FlightCreated;
use App\Models\Computer;
use App\Models\Drone;
use App\Models\Flight;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Event;

class TestSeeder extends Seeder
{
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

        $this->call(FlightTestSeeder::class);

        if (!app()->environment('testing')) {
            foreach (Flight::query()->cursor() as $flight) {
                // @phpstan-ignore-next-line
                Event::dispatch(new FlightCreated($flight));
            }
        }
    }
}
