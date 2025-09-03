<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('affix_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affix_id')->constrained('affixes')->cascadeOnDelete();
            $table->unsignedTinyInteger('tier'); // 1 = meilleur
            $table->integer('min_value');
            $table->integer('max_value');
            $table->unsignedSmallInteger('item_level_min')->default(1);
            $table->unsignedSmallInteger('item_level_max')->default(999);
            $table->unsignedSmallInteger('weight')->default(100);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('affix_tiers');
    }
};
