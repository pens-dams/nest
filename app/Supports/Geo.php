<?php

namespace App\Supports;

use MatanYadaev\EloquentSpatial\Objects\Point;

class Geo
{
    const EARTH_RADIUS = 6371010.0;
    const WORLD_SIZE = M_PI * self::EARTH_RADIUS;

    /**
     * @param array{lat: float, lng: float, alt: float} $point
     * @param array{lat: float, lng: float, alt: float} $referencePoint
     *
     * @return array{x: float, y: float, z: float}
     */
    public static function latLngToVector3Relative(
        array $point,
        array $referencePoint,
    ): array
    {
        [$px, $py] = self::latLngToXY($point);
        [$rx, $ry] = self::latLngToXY($referencePoint);

        $target = [
            'x' => $px - $rx,
            'y' => $py - $ry,
            'z' => $point['alt'] - $referencePoint['alt'],
        ];

        // apply the spherical mercator scale-factor for the reference latitude
        $cosLat = cos(deg2rad($referencePoint['lat']));
        $target['x'] *= $cosLat;
        $target['y'] *= $cosLat;

        return $target;
    }

    /**
     * @param Point $origin
     * @param Point $destination
     * @return float
     */
    public static function getDistanceInMeter(Point $origin, Point $destination): float
    {
        $originLat = $origin->latitude;
        $originLng = $origin->longitude;

        $destinationLat = $destination->latitude;
        $destinationLng = $destination->longitude;

        $earthRadius = self::EARTH_RADIUS;

        $latFrom = deg2rad($originLat);
        $lonFrom = deg2rad($originLng);
        $latTo = deg2rad($destinationLat);
        $lonTo = deg2rad($destinationLng);

        $latDelta = $latTo - $latFrom;

        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    /**
     * @param array{lat: float, lng: float, alt: float} $point
     * @return number[]
     */
    private static function latLngToXY(array $point): array
    {
        return [
            self::EARTH_RADIUS * deg2rad($point['lng']),
            self::EARTH_RADIUS * log(tan(0.25 * M_PI + 0.5 * deg2rad($point['lat']))),
        ];
    }
}
