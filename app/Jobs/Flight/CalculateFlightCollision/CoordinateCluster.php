<?php
/** @noinspection PhpRedundantVariableDocTypeInspection */

namespace App\Jobs\Flight\CalculateFlightCollision;

use App\Models\Flight;
use App\Objects\Coordinate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\LazyCollection;
use App\Exceptions\Flight\CoordinateClusterFault;

class CoordinateCluster
{
    const CLUSTER_COUNT = 100;

    const CLUSTER_CACHE_KEY = 'flight:coordinate:cluster';

    protected string $cacheValidationKey;

    /**
     * @var Collection<int, Collection<int, Collection<int, Coordinate>>> $clusters
     */
    protected Collection $clusters;

    /**
     * @var array{
     *     x: array{min: float, max: float, diff: float, additional: int},
     *     y: array{min: float, max: float, diff: float, additional: int},
     * } $meta
     */
    protected array $meta = [
        'x' => [
            'min' => 0,
            'max' => 0,
            'diff' => 0,
            'additional' => 0,
        ],
        'y' => [
            'min' => 0,
            'max' => 0,
            'diff' => 0,
            'additional' => 0,
        ],
    ];

    public function reload(): void
    {
        if ($this->instanceIsInvalid()) {
            $this->determineCacheOrBuild();
        }
    }

    /**
     * @param int $xPoint
     * @param int $yPoint
     * @return Collection<int, Coordinate>
     * @throws CoordinateClusterFault
     */
    public function resolveClusterFor(int $xPoint, int $yPoint): Collection
    {
        if (! isset($this->cacheValidationKey) || ! isset($this->clusters)) {
            $this->determineCacheOrBuild();
        }

        return $this->getClusterFor($xPoint, $yPoint);
    }

    /**
     * @param int $xPoint
     * @param int $yPoint
     * @return Collection<int, Coordinate>
     * @throws CoordinateClusterFault
     */
    protected function getClusterFor(int $xPoint, int $yPoint): Collection
    {
        /**
         * @param Collection<int, Collection<int, Coordinate>> $clusterYData
         * @return Collection<int, Coordinate>
         * @throws CoordinateClusterFault
         */
        $lookingForY = function (Collection $clusterYData) use ($yPoint): Collection {
            foreach ($clusterYData as $clusterY => $cluster) {
                /** @var Collection<int, Coordinate> $cluster */
                if ($yPoint <= $clusterY + $this->meta['y']['additional'] && $yPoint >= $clusterY) {
                    return $cluster;
                }
            }

            if ($yPoint < $this->meta['y']['min'] && $firstData = $clusterYData->first()) {
                /** @var Collection<int, Coordinate> $firstData */
                return $firstData;
            } elseif ($yPoint > $this->meta['y']['max'] && $lastData = $clusterYData->last()) {
                /** @var Collection<int, Coordinate> $lastData */
                return $lastData;
            }

            throw new CoordinateClusterFault('Coordinate Y not found in cluster: ' . $yPoint);
        };

        foreach ($this->clusters as $clusterX => $clusterYData) {
            if ($xPoint <= $clusterX + $this->meta['x']['additional'] && $xPoint >= $clusterX) {
                return $lookingForY($clusterYData);
            }
        }

        if ($xPoint < $this->meta['x']['min'] && $firstData = $this->clusters->first()) {
            return $lookingForY($firstData);
        } elseif ($xPoint > $this->meta['x']['max'] && $lastData = $this->clusters->last()) {
            return $lookingForY($lastData);
        }

        throw new CoordinateClusterFault('Coordinate X not found in cluster: ' . $xPoint);
    }

    /**
     * @param Collection<int, Coordinate> $coordinates
     * @return void
     */
    protected function buildMetaData(Collection $coordinates): void
    {
        /** @var float $minX */
        $minX = $coordinates->min('x');
        $this->meta['x']['min'] = $minX;
        /** @var float $minY */
        $minY = $coordinates->min('y');
        $this->meta['y']['min'] = $minY;

        /** @var float $maxX */
        $maxX = $coordinates->max('x');
        $this->meta['x']['max'] = $maxX;
        /** @var float $maxY */
        $maxY = $coordinates->max('y');
        $this->meta['y']['max'] = $maxY;

        $this->meta['x']['diff'] = $maxX - $minX;
        $this->meta['y']['diff'] = $maxY - $minY;

        $additionalX = (int)floor(($maxX - $minX) / self::CLUSTER_COUNT) + 1;
        $this->meta['x']['additional'] = $additionalX;
        $additionalY = (int)floor(($maxY - $minY) / self::CLUSTER_COUNT) + 1;
        $this->meta['y']['additional'] = $additionalY;
    }

    /**
     * @param LazyCollection<int, Flight\Log> $logs
     * @return void
     */
    protected function buildCluster(LazyCollection $logs): void
    {
        $coordinates = collect();

        $logs->each(fn(Flight\Log $log) => $coordinates->push(new Coordinate (
            $log->ulid,
            $log->flight_id,
            $log->meta['coordinate']['x'],
            $log->meta['coordinate']['y'],
            $log->meta['coordinate']['z']
        )));

        $this->buildMetaData($coordinates);

        /** @var Collection<int, Collection<int, Collection<int, Coordinate>>> $clusters */
        $clusters = collect();

        for ($x = (int)floor($this->meta['x']['min']); $x <= (int)$this->meta['x']['max']; $x += $this->meta['x']['additional']) {
            $clusterY = collect();
            for ($y = (int)floor($this->meta['y']['min']); $y <= (int)$this->meta['y']['max']; $y += $this->meta['y']['additional']) {
                $clusterY->put($y, collect());
            }

            $clusters->put($x, $clusterY);
        }

        $this->clusters = $clusters;

        $coordinates->each(function (Coordinate $coordinate) {
            $x = (int)floor($coordinate->x);
            $y = (int)floor($coordinate->y);

            $this->getClusterFor($x, $y)->push($coordinate);
        });
    }

    /**
     * @return array{
     *     x: array{min: float, max: float, diff: float, additional: int},
     *     y: array{min: float, max: float, diff: float, additional: int},
     * }
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    private function determineCacheOrBuild(): void
    {
        if ($this->getFromCache()) {
            return;
        }

        if ($this->getQuery()->doesntExist()) {
            $this->clusters = collect();
            return;
        }

        /** @var LazyCollection<int, Flight\Log> $logs */
        $logs = $this->getQuery()->cursor();

        $this->buildCluster($logs);
        $this->saveToCache($logs->last()?->ulid ?? throw new CoordinateClusterFault('No last log found'));
    }

    private function getFromCache(): bool
    {
        /** @var Flight\Log|null $lastLog */
        $lastLog = Flight\Log::query()
            ->where('meta->progress', '>=', 5)
            ->where('meta->progress', '<=', 95)
            ->latest()
            ->first();

        if (! $lastLog) {
            return false;
        }

        if (Cache::has(self::CLUSTER_CACHE_KEY)) {
            /**
             * @var array{
             *     last_log: string,
             *     clusters: Collection<int, Collection<int, Collection<int, Coordinate>>>,
             *     meta: array{
             *          x: array{min: float, max: float, diff: float, additional: int},
             *          y: array{min: float, max: float, diff: float, additional: int},
             *     }
             * } $cache
             */
            $cache = Cache::get(self::CLUSTER_CACHE_KEY);

            if ($cache['last_log'] === $lastLog->ulid) {
                $this->cacheValidationKey = $lastLog->ulid;
                $this->clusters = $cache['clusters'];
                $this->meta = $cache['meta'];

                return true;
            }
        }

        return false;
    }

    private function saveToCache(string $lastUlid): void
    {
        $this->cacheValidationKey = $lastUlid;

        Cache::put(self::CLUSTER_CACHE_KEY, [
            'last_log' => $lastUlid,
            'clusters' => $this->clusters,
            'meta' => $this->meta,
        ], now()->addHours(3));
    }

    /**
     * @return Builder<Flight\Log>
     */
    private function getQuery(): Builder
    {
        return Flight\Log::query()
            ->where('meta->progress', '>=', 5)
            ->where('meta->progress', '<=', 95);
    }

    private function instanceIsInvalid(): bool
    {
        if (! isset($this->cacheValidationKey)) {
            return true;
        }

        /** @var Flight\Log|null $lastLog */
        $lastLog = Flight\Log::query()
            ->where('meta->progress', '>=', 5)
            ->where('meta->progress', '<=', 95)
            ->orderBy('ulid')
            ->first();

        return $lastLog?->ulid !== $this->cacheValidationKey;
    }
}
