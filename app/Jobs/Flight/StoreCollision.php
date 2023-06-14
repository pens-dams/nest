<?php

namespace App\Jobs\Flight;

use App\Models\Flight\Intersect;
use App\Models\Flight\Log;
use App\Objects\Collision;
use App\Objects\Coordinate;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use LogicException;
use MatanYadaev\EloquentSpatial\Objects\Point;

class StoreCollision implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public int $tries = 5;

    public function __construct(
        protected Collision $collision,
        protected ?int      $intersectNumber = null,
    )
    {
        //
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        $collisionPoint = $this->collision->getMiddlePoint();

        $key = null;

        if ($collisionPoint) {
            $key = ".$collisionPoint->x.$collisionPoint->y.$collisionPoint->z";
        }

        return [
            (new WithoutOverlapping('flight-collision'.$key))
                ->releaseAfter(now()->addSeconds($this->intersectNumber ?? 1)),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $coordinates = $this->collision->getCoordinates();

        /** @var Collection<int, Log> $logPoints */
        $logPoints = Log::query()
            ->whereIn('ulid', $coordinates
                ->map(fn(Coordinate $coordinate) => $coordinate->logUlid))
            ->get();

        if ($this->alreadyExistsByLogPoints($logPoints)) {
            return;
        }

        if ($intersection = $this->getIntersectionByCoordinateMiddlePoint()) {
            $this->applyIntersectionToLogs($intersection, $logPoints);
            // only one intersection per collision
            return;
        }

        $pointA = $logPoints->first()?->position;
        $altitudeA = $logPoints->first()?->altitude;

        $pointB = $logPoints->last()?->position;
        $altitudeB = $logPoints->last()?->altitude;

        if (!$pointA && !$pointB) {
            throw new LogicException('collision coordinate count is not 2');
        }

        $middleLat = ($pointA?->latitude + $pointB?->latitude) / 2;
        $middleLng = ($pointA?->longitude + $pointB?->longitude) / 2;
        $middleAlt = ($altitudeA + $altitudeB) / 2;

        $intersection = new Intersect();

        $intersection->intersect = new Point($middleLat, $middleLng);
        $intersection->altitude = $middleAlt;

        $intersection->radius = Coordinate::$collisionThreshold;
//        $intersection->collision_time = $this->collision->getCollisionTime();

        $intersection->meta = [
            'lla' => [
                'lat' => $middleLat,
                'lng' => $middleLng,
                'alt' => $middleAlt,
            ],
            'point_a' => [
                'lat' => $pointA?->latitude,
                'lng' => $pointA?->longitude,
                'alt' => $altitudeA,
            ],
            'point_b' => [
                'lat' => $pointB?->latitude,
                'lng' => $pointB?->longitude,
                'alt' => $altitudeB,
            ],
            'collision' => $this->collision->toArray(),
        ];

        $intersection->save();

        $this->applyIntersectionToLogs($intersection, $logPoints);
    }

    /**
     * @param Collection<int, Log> $logPoints
     * @return bool
     */
    private function alreadyExistsByLogPoints(Collection $logPoints): bool
    {
        $intersectionQuery = Intersect::query();

        foreach ($logPoints as $logPoint) {
            $intersectionQuery->whereHas('logs', fn($query) => $query->where('ulid', $logPoint->ulid));
        }

        return $intersectionQuery->exists();
    }

    private function getIntersectionByCoordinateMiddlePoint(): ?Intersect
    {
        $middlePoint = $this->collision->getMiddlePoint();

        if (!$middlePoint) {
            return null;
        }

        $intersectionQuery = Intersect::query();

        $intersectionQuery->where('meta->collision->middlePoint->x', $middlePoint->x);
        $intersectionQuery->where('meta->collision->middlePoint->y', $middlePoint->y);
        $intersectionQuery->where('meta->collision->middlePoint->z', $middlePoint->z);

        /** @var Intersect|null */
        return $intersectionQuery->first();
    }

    /**
     * @param Intersect $intersection
     * @param Collection<int, Log> $logPoints
     * @return void
     */
    private function applyIntersectionToLogs(Intersect $intersection, Collection $logPoints): void
    {
        $intersection->logs()->syncWithoutDetaching($logPoints->pluck('ulid')->unique()->toArray());
        $intersection->flights()->syncWithoutDetaching($logPoints->pluck('flight_id')->unique()->toArray());
        $intersection->paths()->syncWithoutDetaching($logPoints->pluck('path_id')->unique()->toArray());
    }
}
