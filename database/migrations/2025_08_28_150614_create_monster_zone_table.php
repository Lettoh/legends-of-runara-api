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
        Schema::create('monster_zone', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained('zone')->cascadeOnDelete();
            $table->foreignId('monster_id')->constrained('monster')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['zone_id', 'monster_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monster_zone');
    }
};
