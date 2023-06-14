<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use League\Csv\Reader;

class FlightTestSeeder extends Seeder
{
    use WithoutModelEvents;
    use Concern\CSVRestorer;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->restoreCsvToTable('flights', database_path('seeders/data/flights.csv'));
        $this->restoreCsvToTable('flight_logs', database_path('seeders/data/flight_logs.csv'));
    }
}
