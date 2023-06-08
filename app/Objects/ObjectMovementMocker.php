<?php

namespace App\Objects;

use App\Supports\Geo;

class ObjectMovementMocker
{
    private int $heading;

    public function __construct(
        protected float $latitude,
        protected float $longitude,
        protected float $altitude,
    )
    {
        $this->heading = 1;
    }

    public function turn(int $degree): void
    {
        $this->heading += $degree;
    }

    public function advance(int $distanceInMeter): void
    {
        $earthRadius = Geo::EARTH_RADIUS;
        $distance = $distanceInMeter / $earthRadius;

        $bearing = deg2rad($this->heading);
        $startLatitude = deg2rad($this->latitude);
        $startLongitude = deg2rad($this->longitude);

        $newLatitude = asin(sin($startLatitude) * cos($distance) + cos($startLatitude) * sin($distance) * cos($bearing));
        $newLongitude = $startLongitude + atan2(sin($bearing) * sin($distance) * cos($startLatitude), cos($distance) - sin($startLatitude) * sin($newLatitude));

        $this->latitude = rad2deg($newLatitude);
        $this->longitude = rad2deg($newLongitude);
    }

    /**
     * @return float
     */
    public function getLongitude(): float
    {
        return $this->longitude;
    }

    /**
     * @return float
     */
    public function getLatitude(): float
    {
        return $this->latitude;
    }
}
