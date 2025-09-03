<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('spell_status_effects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spell_id')->constrained()->cascadeOnDelete();

            // 'defense','damage','elemental_weakness','stun','shield','dot'
            $table->string('kind');

            // 'percent' (%) ou 'flat' (valeur brute)
            $table->enum('mode', ['percent','flat'])->default('percent');

            // valeur signée (ex: -30 pour -30% DMG, +30 pour +30% DMG)
            $table->integer('value')->nullable();

            // pour weakness: 'strength' ou 'power'
            $table->enum('vs', ['strength','power'])->nullable();

            $table->unsignedTinyInteger('duration_turns')->default(0);
            $table->unsignedTinyInteger('chance')->default(100);

            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('spell_status_effects');
    }
};
