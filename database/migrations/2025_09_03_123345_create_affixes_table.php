<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('affixes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();     // ex: inc_crit_chance, flat_power, inc_speed
            $table->string('name');
            $table->string('stat_code');          // 'strength','power','defense','hp','speed','crit_chance','crit_damage'
            $table->enum('kind', ['prefix','suffix'])->default('prefix');
            $table->enum('effect', ['add','percent'])->default('add'); // add=flat points, percent=increased %
            $table->unsignedTinyInteger('max_per_item')->default(1);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('affixes');
    }
};
