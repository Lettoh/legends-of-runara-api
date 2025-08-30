<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('idle_run_monsters', function (Blueprint $t) {
            $t->id();
            $t->foreignId('idle_run_id')->constrained()->cascadeOnDelete();
            $t->foreignId('monster_id')->constrained('monsters');
            $t->unsignedInteger('count')->default(0);
            $t->timestamp('last_at')->nullable();
            $t->unique(['idle_run_id','monster_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('idle_run_monsters');
    }
};

