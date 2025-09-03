<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('character_type_spell', function (Blueprint $table) {
            $table->id();

            $table->unsignedSmallInteger('character_type_id');
            $table->foreign('character_type_id')
                ->references('id')->on('character_types')
                ->cascadeOnDelete();

            $table->foreignId('spell_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('unlock_level')->default(1);
            $table->string('required_specialization')->nullable();
            $table->unique(['character_type_id','spell_id','required_specialization'], 'cts_unique');
            $table->timestamps();
        });

    }
    public function down(): void {
        Schema::dropIfExists('character_type_spell');
    }
};
