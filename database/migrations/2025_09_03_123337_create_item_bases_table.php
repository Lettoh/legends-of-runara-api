<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('item_bases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slot_id')->constrained('equipment_slots');
            $table->string('name');
            $table->unsignedSmallInteger('ilvl_req')->default(1);
            $table->string('image')->nullable();

            // implicite inchangeable (ex: power +min/max)
            $table->string('implicit_stat_code')->nullable();
            $table->integer('implicit_min')->nullable();
            $table->integer('implicit_max')->nullable();

            // pour les armes uniquement ; 0.00 pour les autres
            $table->decimal('base_crit_chance', 5, 2)->default(0.00);

            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('item_bases');
    }
};
