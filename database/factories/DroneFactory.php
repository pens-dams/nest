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
        ['lat' => -7.275884, 'lng' => 112.794072], // parkir pens
        ['lat' => -7.279340, 'lng' => 112.790494], // air mancur its
        ['lat' => -7.283945, 'lng' => 112.794104], // kantin ITS
        ['lat' => -7.285384, 'lng' => 112.792468], // stadion ITS
        ['lat' => -7.280232, 'lng' => 112.799302], // BRIN Office
        ['lat' => -7.278976, 'lng' => 112.797511], // Danau Informatika
        ['lat' => -7.276804, 'lng' => 112.795435], // PENS Futsal
        ['lat' => -7.278065, 'lng' => 112.794389], // Masjid PPNS
        ['lat' => -7.279732, 'lng' => 112.794410], // Klinik ITS
        ['lat' => -7.276985, 'lng' => 112.793053], // pascasarjana pens
        ['lat' => -7.282329, 'lng' => 112.793080], // masjid ITS
//        ['lat' => -7.319234, 'lng' => 112.784829], // masjid pandugo
//        ['lat' => -7.272505, 'lng' => 112.766000], // masjid jojoran
//        ['lat' => -7.265209, 'lng' =>  112.769074], // smk 5
//        ['lat' => -7.267983, 'lng' =>  112.757149], // RS Dr Soetomo
//        ['lat' => -7.270996, 'lng' => 112.758667], // S2 Airlangga
//        ['lat' => -7.303987, 'lng' =>  112.799978], // Gerbang TPU Keputih
//        ['lat' => -7.308350, 'lng' =>  112.814462], // Green Semanggi Mangrove
//        ['lat' => -7.295154, 'lng' =>  112.803476], // Taman Harmony Keputih
//        ['lat' => -7.276188, 'lng' => 112.779733], // Galaxy Mall 1
//        ['lat' => -7.275932, 'lng' => 112.782147], // Galaxy Mall 2
//        ['lat' => -7.274091, 'lng' => 112.771171], // Atlas Sport Club
//        ['lat' => -7.285930, 'lng' => 112.750084], // rumah jul
//        ['lat' => -7.284259, 'lng' => 112.759547], // muhammadiyah sekolah (mas yuqi)
//        ['lat' => -7.285874, 'lng' => 112.761977], // RS Menur
//        ['lat' => -7.289980, 'lng' => 112.770356], // gardu PLN Sukolilo
//        ['lat' => -7.320755, 'lng' => 112.769938], // transmart rungkut
//        ['lat' => -7.320937, 'lng' => 112.767652], // universitas surabaya
//        ['lat' => -7.326822, 'lng' => 112.761161], // rungkut industri
//        ['lat' => -7.321757, 'lng' => 112.783928], // pandugo kuliner
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
        $point = new Point($location['lat'], $location['lng']);

        return [
            'name' => $this->faker->lastName() . '\'s drone.',
            'serial_number' => $this->faker->macAddress(),
            'photo_path' => $this->faker->randomElement([
                '/storage/build/drone-hi-end.jpg',
                '/storage/build/drone-mini.jpg',
                '/storage/build/quadcopter.jpg',
            ]),
            'standby_location' => $point,
            'meta' => $point->toArray(),
        ];
    }
}
