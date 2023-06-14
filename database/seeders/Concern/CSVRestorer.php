<?php

namespace Database\Seeders\Concern;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use League\Csv\Reader;

trait CSVRestorer
{

    /**
     * @param string $tableName
     * @param string $filePath
     * @return void
     * @throws \League\Csv\Exception
     * @throws \League\Csv\UnavailableStream
     */
    private function restoreCsvToTable(string $tableName, string $filePath): void
    {
        $reader = Reader::createFromPath($filePath, 'r');

        $reader->setHeaderOffset(0);
        $records = $reader->getRecords();

        collect($records)
            ->chunk(500)
            ->each(function (Collection $chunkedRecords) use ($tableName) {
                $data = $chunkedRecords->toArray();

                foreach ($data as $index => $records) {
                    foreach ($records as $column => $value) {
                        // 0x000000000101000000A9BF5E61C1325C4024EEB1F4A11B1DC0
                        if (Str::startsWith($value, '0x')) {
                            $value = Str::replaceFirst('0x000000000', '0', $value);

                            $data[$index][$column] = DB::raw("ST_GeomFromWKB(X'{$value}')");
                        }

                        if (is_numeric($value)) {
                            $data[$index][$column] = (int)$value;
                        }

                        if (empty($value)) {
                            $data[$index][$column] = null;
                        }
                    }
                }
                DB::table($tableName)->insert($data);
            });
    }
}
