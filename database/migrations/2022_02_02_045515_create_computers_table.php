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
        Schema::create('computers', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->references('id')->on('teams');
            $table->string('name');
            $table->string('state')->default('disconnected');
            $table->text('description')->nullable();
            $table->string('ip')->nullable();
            $table->string('location')->nullable();
            $table->point('position')->nullable();
            $table->dateTime('latest_handshake')->nullable();
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
        Schema::dropIfExists('computers');
    }
};
