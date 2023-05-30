<?php

namespace App\Jobs\Flight;

use App\Models\Flight;
use App\Models\Flight\Log;
use App\Objects\Collision;
use App\Objects\Coordinate;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateAllCollision implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $data = Flight::query()
            ->pluck('ulid')
            // @phpstan-ignore-next-line
            ->mapWithKeys(function (string $flightUlid): array {
                $logQuery = Log::query()
                    ->where('flight_id', $flightUlid)
                    ->where('meta->progress', '>=', 5)
                    ->where('meta->progress', '<=', 95)
                    ->orderBy('ulid');

                $positions = $logQuery->get();

                $data = $positions->map(fn(Log $log) => new Coordinate (
                    $log->ulid,
                    $log->flight_id,
                    $log->meta['coordinate']['x'],
                    $log->meta['coordinate']['y'],
                    $log->meta['coordinate']['z']
                ));

                return [$flightUlid => $data];
            });

        foreach ($data as $flightId => $coordinates) {
            $otherData = $data->except([$flightId]);

            foreach ($otherData as $comparableCoordinates) {
                foreach ($comparableCoordinates as $comparableCoordinate) {
                    foreach ($coordinates as $log) {
                        if (($distance = $log->getDistance($comparableCoordinate)) < Coordinate::$collisionThreshold) {
                            $collision = new Collision([$log, $comparableCoordinate], $distance);
                            $this->storeCollision($collision);
                        }
                    }
                }
            }
        }
    }

    public function storeCollision(Collision $collision): void
    {
        if ($this->batch()) {
            $this->batch()->add(new StoreCollision($collision));
        } else {
            StoreCollision::dispatch($collision);
        }
    }
}
