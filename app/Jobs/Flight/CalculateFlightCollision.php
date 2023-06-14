<?php /** @noinspection PhpRedundantVariableDocTypeInspection */

namespace App\Jobs\Flight;

use App\Exceptions\Flight\CoordinateClusterFault;
use App\Models\Flight;
use App\Objects\Collision;
use App\Objects\Coordinate;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class CalculateFlightCollision implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public int $tries = 10;

    public int $collisionCount = 0;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Flight $flight,
    )
    {
    }

    /**
     * Execute the job.
     */
    public function handle(CalculateFlightCollision\CoordinateCluster $coordinateCluster): void
    {
        $coordinateCluster->reload();

        $flightUlid = $this->flight->ulid;

        $this->cleanupOldCollisions();

        $flightLogs = $this->flight->logs()
            ->where('meta->progress', '>=', 5)
            ->where('meta->progress', '<=', 95)
            ->orderBy('ulid')
            ->get();

        $pointingCoordinates = $flightLogs->map(fn($log) => new Coordinate (
            $log->ulid,
            $log->flight_id,
            $log->meta['coordinate']['x'],
            $log->meta['coordinate']['y'],
            $log->meta['coordinate']['z']
        ));

        foreach ($pointingCoordinates as $pointingCoordinate) {
            /** @var Coordinate $pointingCoordinate */
            $x = (int)floor($pointingCoordinate->x);
            $y = (int)floor($pointingCoordinate->y);

            try {
                $comparableCoordinates = $coordinateCluster->resolveClusterFor($x, $y);
            } catch (CoordinateClusterFault $e) {
                Log::warning($e->getMessage(), [
                    'x' => $x,
                    'y' => $y,
                    'meta' => $coordinateCluster->getMeta(),
                ]);
                continue;
            }

            foreach ($comparableCoordinates as $comparableCoordinate) {
                if ($comparableCoordinate->flight === $flightUlid) {
                    continue;
                }

                /** @var Coordinate $comparableCoordinate */
                $distance = $pointingCoordinate->getEuclideanDistance($comparableCoordinate);

                if ($distance < Coordinate::$collisionThreshold) {
                    $collision = new Collision([$pointingCoordinate, $comparableCoordinate], $distance);
                    $this->storeCollision($collision);
                }
            }
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return ['flight-collision', 'flight:' . $this->flight->ulid];
    }

    public function storeCollision(Collision $collision): void
    {
        $this->collisionCount++;

        $job = new StoreCollision($collision, $this->collisionCount);

        if ($this->batch()) {
            $this->batch()->add($job);
        } else {
            Bus::dispatch($job);
        }
    }

    private function cleanupOldCollisions(): void
    {
        $intersections = $this->flight->intersections()->cursor();

        foreach ($intersections as $intersection) {
            /** @var Flight\Intersect $intersection */

            if ($intersection->flights()->count() === 2) {
                $intersection->delete();

                continue;
            }

            $intersection->flights()->detach($this->flight->ulid);
        }
    }
}
