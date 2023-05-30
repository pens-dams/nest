<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flights', function (Blueprint $table) {
            $table->ulid()->primary();

            $table->foreignId('drone_id')
                ->constrained()
                ->references('id')
                ->on('drones');

            $table->string('code');

            $table->integer('sequence')->default(1);

            $table->dateTime('departure');

            $table->point('from');
            $table->point('to');

            $table->integer('planned_altitude')->nullable();
            $table->integer('speed')->nullable();

            $table->string('name')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flights');
    }
};
