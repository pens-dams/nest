<?php

namespace Database\Factories;

use App\Models\Computer;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use MatanYadaev\EloquentSpatial\Objects\Point;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Computer>
 */
class ComputerFactory extends Factory
{
    protected $model = Computer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $city = $this->faker->city();

        return [
            'name' => $city.' Edge Computing.',
            'ip' => $this->faker->ipv4(),
            'latest_handshake' => now(),
            'location' => $city.', Indonesia',
            'position' => new Point(
                $this->faker->latitude(),
                $this->faker->longitude(),
            ),
        ];
    }

    public function configure(): static
    {
        $availableTeam = Team::query()->whereDoesntHave('computers')->latest()->firstOrFail();

        return $this
            ->for($availableTeam, 'team')
            ->afterCreating(fn(Computer $computer) => $computer->createToken('main'));
    }
}
