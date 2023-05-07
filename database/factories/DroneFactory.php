<?php

namespace Database\Factories;

use App\Models\Drone;
use Illuminate\Database\Eloquent\Factories\Factory;
use MatanYadaev\EloquentSpatial\Objects\Point;

/**
 * @extends Factory<Drone>
 */
class DroneFactory extends Factory
{
    /**
     * @var array<int, array{lat: float, lng: float}> $knownLocations
     */
    public static array $knownLocations = [
//        ['lat' => -7.2756967, 'lng' => 112.7761407],
//        ['lat' => -7.2619491, 'lng' => 112.7478422],
//        ['lat' => -7.2627836, 'lng' => 112.745902],
//        ['lat' => -7.2623683, 'lng' => 112.7362544],
//        ['lat' => -7.285015, 'lng' => 112.739325],
//        ['lat' => -7.3138113, 'lng' => 112.7333834],
//        ['lat' => -7.2930221, 'lng' => 112.7171467],
//        ['lat' => -7.3070382, 'lng' => 112.6952806],
        ['lat' => -7.275884, 'lng' => 112.794072], // parkir pens
        ['lat' => -7.279340, 'lng' => 112.790494], // air mancur its
        ['lat' => -7.319234, 'lng' => 112.784829], // masjid pandugo
        ['lat' => -7.272505, 'lng' => 112.766000], // masjid jojoran
        ['lat' => -7.265209, 'lng' =>  112.769074], // smk 5
        ['lat' => -7.267983, 'lng' =>  112.757149], // RS Dr Soetomo
        ['lat' => -7.270996, 'lng' => 112.758667], // S2 Airlangga
        ['lat' => -7.303987, 'lng' =>  112.799978], // Gerbang TPU Keputih
        ['lat' => -7.308350, 'lng' =>  112.814462], // Green Semanggi Mangrove
        ['lat' => -7.295154, 'lng' =>  112.803476], // Taman Harmony Keputih
        ['lat' => -7.276188, 'lng' => 112.779733], // Galaxy Mall 1
        ['lat' => -7.275932, 'lng' => 112.782147], // Galaxy Mall 2
        ['lat' => -7.274091, 'lng' => 112.771171], // Atlas Sport Club
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        /** @var array{lat: float, lng: float} $location */
        $location = $this->faker->randomElement(self::$knownLocations);

        return [
            'name' => $this->faker->lastName() . '\'s drone.',
            'serial_number' => $this->faker->macAddress(),
            'photo_path' => $this->faker->randomElement([
                '/storage/build/drone-hi-end.jpg',
                '/storage/build/drone-mini.jpg',
                '/storage/build/quadcopter.jpg',
            ]),
            'standby_location' => new Point($location['lat'], $location['lng']),
        ];
    }
}
