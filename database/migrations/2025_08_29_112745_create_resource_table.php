<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('resource', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();           // image
            $table->unsignedTinyInteger('rarity')->default(1); // 1..5
            $table->boolean('tradeable')->default(true);
            $table->timestamps();
        });

        Schema::create('monster_resource', function (Blueprint $table) {
            $table->foreignId('monster_id')->constrained('monster')->cascadeOnDelete();
            $table->foreignId('resource_id')->constrained('resource')->cascadeOnDelete();
            $table->decimal('drop_chance', 5, 2)->default(100); // 0..100
            $table->unsignedSmallInteger('min_qty')->default(1);
            $table->unsignedSmallInteger('max_qty')->default(1);
            $table->timestamps();
            $table->primary(['monster_id','resource_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('monster_resource');
        Schema::dropIfExists('resource');
    }
};
