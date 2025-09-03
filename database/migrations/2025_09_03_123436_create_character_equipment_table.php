<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('character_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained('characters')->cascadeOnDelete();
            $table->foreignUlid('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('slot_id')->constrained('equipment_slots');
            $table->timestamp('equipped_at')->nullable();
            $table->unique(['character_id','slot_id']);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('character_equipment');
    }
};
