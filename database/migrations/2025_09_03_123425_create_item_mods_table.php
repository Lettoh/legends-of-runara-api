<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('item_mods', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('affix_id')->constrained('affixes');
            $table->foreignId('tier_id')->constrained('affix_tiers');
            $table->integer('value');
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('item_mods');
    }
};
