<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drone_id')->constrained()->references('id')->on('drones');
            $table->string('code');
            $table->dateTime('departure_time');
            // latitudes and longitudes

            $table->point('latitude_from');
            $table->point('longitude_from');
            $table->point('latitude_to');
            $table->point('longitude_to');
            $table->integer('planned_altitude')->nullable();
            $table->integer('speed')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('flights');
    }
};
