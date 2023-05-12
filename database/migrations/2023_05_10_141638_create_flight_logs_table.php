<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('flight_logs', function (Blueprint $table) {
            $table->ulid();

            $table->foreignId('flight_id')
                ->constrained()
                ->references('id')
                ->on('flights')
                ->cascadeOnDelete();

            $table->point('position');
            $table->integer('altitude');
            $table->integer('speed');

            $table->timestamp('datetime');

            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_logs');
    }
};
