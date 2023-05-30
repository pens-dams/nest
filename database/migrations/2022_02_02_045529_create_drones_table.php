<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('drones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->references('id')->on('teams');
            $table->foreignId('compute_id')->constrained()->references('id')->on('computers');
            $table->string('name')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('photo_path')->nullable();

            $table->point('standby_location')->nullable();

            $table->jsonb('meta')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('drones');
    }
};
