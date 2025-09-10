<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('item_base_recipe_ingredients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('base_id');
            $table->unsignedBigInteger('resource_id');
            $table->unsignedInteger('quantity');
            $table->timestamps();

            $table->foreign('base_id')->references('id')->on('item_bases')->cascadeOnDelete();
            $table->foreign('resource_id')->references('id')->on('resources')->restrictOnDelete();
            $table->unique(['base_id','resource_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('item_base_recipe_ingredients');
    }
};
