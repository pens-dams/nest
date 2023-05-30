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
        Schema::create('flight_paths', function (Blueprint $table) {
            $table->ulid();
            $table->foreignUlid('flight_id')->constrained('flights', 'ulid')->cascadeOnDelete();
            $table->unsignedInteger('sequence');
            $table->point('position');
            $table->double('altitude');
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_paths');
    }
};
