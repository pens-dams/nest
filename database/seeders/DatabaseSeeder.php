<?php

namespace Database\Seeders;

use App\Models\Computer;
use App\Models\Drone;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
             ->afterCreating(fn (Computer $computer) => Drone::factory()
                 ->for($computer, 'compute')
                 ->for($user->currentTeam()->firstOrFail(), 'team')
                 ->count(3)
                 ->create()
             )
             ->create();
    }
}
