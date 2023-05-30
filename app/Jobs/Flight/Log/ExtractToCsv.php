<?php

namespace App\Jobs\Flight\Log;

use App\Models\Flight\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use League\Csv\CannotInsertRecord;
use League\Csv\Exception;
use League\Csv\UnavailableStream;
use League\Csv\Writer;

class ExtractToCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string|null $filename = null,
    )
    {
        //
    }

    /**
     * Execute the job.
     * @throws UnavailableStream
     * @throws CannotInsertRecord
     * @throws Exception
     */
    public function handle(): void
    {
        /** @var LazyCollection<int, Log> $data */
        $data = Log::query()->cursor();

        $writer = Writer::createFromPath($this->getStoredPath(), 'w+');

        // create columns
        $writer->insertOne(['id', 'x', 'y', 'z', 'time']);

        foreach ($data as $datum) {
            $meta = $datum->meta;

            $writer->insertOne([
                $datum->ulid,
                Arr::get($meta, 'coordinate.x'),
                Arr::get($meta, 'coordinate.y'),
                Arr::get($meta, 'coordinate.z'),
                $datum->datetime->getTimestamp(),
            ]);
        }
    }

    protected function getStoredPath(): string
    {
        $filename = Str::replaceLast('.csv', '', $this->filename ?? Str::ulid());

        $path = storage_path('app/logs/' . $filename . '.csv');

        $directory = dirname($path);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        return $path;
    }
}
