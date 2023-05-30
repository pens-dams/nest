<?php

namespace App\Objects;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

/**
 * Class Coordinate
 * @package App\Objects
 *
 * @property string $ulid
 * @property string $flight
 * @property float  $x
 * @property float  $y
 * @property float  $z
 *
 * @phpstan-implements Arrayable<string, string|float>
 */
class Coordinate implements Arrayable, JsonSerializable, Jsonable
{
    public static float $collisionThreshold = 2.;

    public function __construct(
        public string $logUlid,
        public string $flight,
        public float  $x,
        public float  $y,
        public float  $z,
    )
    {
    }

    public function getDistance(Coordinate $coordinate): float
    {
        $x = $this->x - $coordinate->x;
        $y = $this->y - $coordinate->y;
        $z = $this->z - $coordinate->z;

        return sqrt(pow($x, 2) + pow($y, 2) + pow($z, 2));
    }

    public function isCollided(Coordinate $coordinate): bool
    {
        // using Euclidean distance formula to calculate distance between two points
        $distance = $this->getDistance($coordinate);

        return $distance <= self::$collisionThreshold;
    }

    /**
     * @return array{
     *     ulid: string,
     *     flight: string,
     *     x: float,
     *     y: float,
     *     z: float,
     * }
     */
    public function toArray(): array
    {
        return [
            'ulid' => $this->logUlid,
            'flight' => $this->flight,
            'x' => $this->x,
            'y' => $this->y,
            'z' => $this->z,
        ];
    }

    /**
     * @param int $options
     * @return string|false
     */
    public function toJson($options = 0): string|false
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * @return array{
     *     ulid: string,
     *     flight: string,
     *     x: float,
     *     y: float,
     *     z: float,
     * }
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
