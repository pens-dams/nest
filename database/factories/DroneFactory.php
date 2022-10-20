<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Drone>
 */
class DroneFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->lastName().'\'s drone.',
            'serial_number' => $this->faker->macAddress(),
            'photo_path' => $this->faker->randomElement([
                '/storage/build/drone-hi-end.jpg',
                '/storage/build/drone-mini.jpg',
                '/storage/build/quadcopter.jpg',
            ]),
        ];
    }
}
