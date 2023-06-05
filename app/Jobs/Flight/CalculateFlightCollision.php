<?php

namespace App\Jobs\Flight;

use App\Models\Flight;
use App\Objects\Collision;
use App\Objects\Coordinate;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;

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
    public function handle(): void
    {
        $flightUlid = $this->flight->ulid;

        $logQuery = fn (Builder $query) => $query
            ->where('meta->progress', '>=', 5)
            ->where('meta->progress', '<=', 95);

        $flightLogs = $logQuery($this->flight->logs())
            ->orderBy('ulid')
            ->get();

        $otherFlightLogs = $logQuery(Flight\Log::query())
            ->where('flight_id', '!=', $flightUlid)
            ->orderBy('flight_id')
            ->cursor();

        $coordinates = $flightLogs->map(fn($log) => new Coordinate (
            $log->ulid,
            $log->flight_id,
            $log->meta['coordinate']['x'],
            $log->meta['coordinate']['y'],
            $log->meta['coordinate']['z']
        ));

        $comparableCoordinates = collect();

        // @phpstan-ignore-next-line
        $otherFlightLogs->each(fn(Flight\Log $log) => $comparableCoordinates->push(new Coordinate (
            $log->ulid,
            $log->flight_id,
            $log->meta['coordinate']['x'],
            $log->meta['coordinate']['y'],
            $log->meta['coordinate']['z']
        )));

        foreach ($comparableCoordinates as $comparableCoordinate) {
            foreach ($coordinates as $log) {
                if (($distance = $log->getEuclideanDistance($comparableCoordinate)) < Coordinate::$collisionThreshold) {
                    $collision = new Collision([$log, $comparableCoordinate], $distance);
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
        return ['flight-collision', 'flight:'.$this->flight->ulid];
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
}
