<?php

namespace App\Objects;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use JsonSerializable;

/**
 * @implements Arrayable<string, mixed>
 */
class Collision implements Arrayable, JsonSerializable, Jsonable
{
    /**
     * @param array<int, Coordinate> $coordinates
     */
    public function __construct(
        protected array $coordinates,
        protected float $distance,
    )
    {
    }

    public function getMiddlePoint(): Coordinate|null
    {
        if (count($this->coordinates) !== 2) {
            return null;
        }

        $first = $this->coordinates[0];
        $second = $this->coordinates[1];

        return new Coordinate(
            $first->logUlid,
            $first->flight,
            ($first->x + $second->x) / 2,
            ($first->y + $second->y) / 2,
            ($first->z + $second->z) / 2,
        );
    }

    /**
     * @return array{
     *     coordinates: array<int, array{
     *     ulid: string,
     *     flight: string,
     *     x: float,
     *     y: float,
     *     z: float,
     *     }>,
     *     distance: float,
     *  }
     */
    public function toArray(): array
    {
        return [
            'coordinates' => array_map(fn(Coordinate $coordinate) => $coordinate->toArray(), $this->coordinates),
            'distance' => $this->distance,
            'middlePoint' => $this->getMiddlePoint()?->toArray() ?? null,
        ];
    }

    /**
     * @param int $options
     * @return false|string
     */
    public function toJson($options = 0): string|false
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * @return array{
     *     coordinates: array<int, array{
     *     ulid: string,
     *     flight: string,
     *     x: float,
     *     y: float,
     *     z: float,
     *     }>,
     *     distance: float,
     *  }
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return Collection<int, Coordinate>
     */
    public function getCoordinates(): Collection
    {
        return collect($this->coordinates);
    }

    /**
     * @return float
     */
    public function getDistance(): float
    {
        return $this->distance;
    }
}
