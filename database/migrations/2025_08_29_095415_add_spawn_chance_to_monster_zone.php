<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('monster_zone', function (Blueprint $table) {
            $table->decimal('spawn_chance', 5, 2)->default(100)->after('zone_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monster_zone', function (Blueprint $table) {
            $table->dropColumn('spawn_chance');
        });
    }
};
