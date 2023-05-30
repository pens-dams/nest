<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('flight_intersects', function (Blueprint $table) {
            $table->ulid()->primary();

            $table->point('intersect');
            $table->float('altitude');
            $table->float('radius');

            $table->jsonb('meta')->nullable();

            $table->timestamp('collision_time')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_intersects');
    }
};
