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
        Schema::table('flight_logs', function (Blueprint $table) {
            $table->foreignUlid('path_id')
                ->nullable()
                ->after('flight_id')
                ->constrained()
                ->references('ulid')
                ->on('flight_paths')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flight_logs', function (Blueprint $table) {
            $table->dropForeign(['path_id']);
            $table->dropColumn('path_id');
        });
    }
};
