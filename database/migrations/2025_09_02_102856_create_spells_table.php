<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('spells', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image')->unique()->nullable();
            $table->text('description')->nullable();

            $table->enum('target', ['enemy_single','enemy_all','ally_single','ally_all','self'])
                ->default('enemy_single');
            $table->unsignedSmallInteger('base_power')->default(0);
            $table->decimal('scaling_str', 6, 3)->default(0); // ex: Guerrier 1.2
            $table->decimal('scaling_pow', 6, 3)->default(0); // ex: Mage 1.0 ; Archer 0.6/0.6
            $table->unsignedTinyInteger('cooldown_turns')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('spells');
    }
};

