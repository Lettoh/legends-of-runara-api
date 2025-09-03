<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('base_id')->constrained('item_bases');
            $table->foreignId('owner_user_id')->constrained('users');
            $table->unsignedSmallInteger('item_level')->default(1);
            $table->enum('rarity', ['normal','magic','rare','unique'])->default('rare');
            $table->integer('implicit_value')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('items');
    }
};
