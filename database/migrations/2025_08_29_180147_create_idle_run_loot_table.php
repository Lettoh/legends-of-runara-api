<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('idle_run_loot', function (Blueprint $t) {
            $t->id();
            $t->foreignId('idle_run_id')->constrained()->cascadeOnDelete();
            $t->foreignId('resource_id')->constrained('resources');
            $t->unsignedInteger('qty')->default(0);
            $t->unique(['idle_run_id','resource_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('idle_run_loot');
    }
};

