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
        Schema::create('flight_intersectable', function (Blueprint $table) {
            $table->foreignUlid('intersect_ulid')
                ->constrained('flight_intersects', 'ulid')
                ->cascadeOnDelete();

            $table->ulidMorphs('intersectable');

            $table->primary(['intersect_ulid', 'intersectable_id', 'intersectable_type'], 'flight_intersectable_primary');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_intersectable');
    }
};
