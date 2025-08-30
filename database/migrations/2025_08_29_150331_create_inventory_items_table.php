<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resource_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('quantity')->default(0);
            $table->boolean('is_locked')->default(false); // ex: objet verrouillé (quête, anti-vente, etc.)
            $table->timestamps();

            $table->unique(['user_id', 'resource_id']); // 1 ligne par (user, resource)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};

