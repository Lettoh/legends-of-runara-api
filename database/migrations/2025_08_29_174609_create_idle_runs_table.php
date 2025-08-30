<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('idle_runs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('zone_id')->constrained('zones')->cascadeOnDelete();

            $t->json('team_snapshot');
            $t->unsignedInteger('duration_sec');
            $t->dateTime('start_at');
            $t->dateTime('end_at');

            $t->enum('status', ['running','finished','claimed','canceled'])
                ->default('running')->index();

            $t->unsignedInteger('seed');
            $t->unsignedSmallInteger('encounters_total');
            $t->unsignedSmallInteger('interval_sec');
            $t->unsignedSmallInteger('encounters_done')->default(0);

            $t->json('loot_summary')->nullable();
            $t->unsignedInteger('gold_earned')->default(0);
            $t->unsignedInteger('xp_earned')->default(0);

            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('idle_runs');
    }
};
