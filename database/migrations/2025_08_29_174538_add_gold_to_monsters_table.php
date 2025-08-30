<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('monsters', function (Blueprint $t) {
            $t->unsignedInteger('gold_min')->default(1);
            $t->unsignedInteger('gold_max')->default(6);
        });
    }
    public function down(): void {
        Schema::table('monsters', function (Blueprint $t) {
            $t->dropColumn(['gold_min','gold_max']);
        });
    }
};
