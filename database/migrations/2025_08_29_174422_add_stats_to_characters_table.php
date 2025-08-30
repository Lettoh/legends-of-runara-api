<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('characters', function (Blueprint $t) {
            $t->unsignedInteger('hp')->default(100);
            $t->unsignedInteger('strength')->default(10);
            $t->unsignedInteger('power')->default(10);
            $t->unsignedInteger('defense')->default(10);
            $t->unsignedBigInteger('xp')->default(0);
        });
    }
    public function down(): void {
        Schema::table('characters', function (Blueprint $t) {
            $t->dropColumn(['hp','strength','power','defense','xp']);
        });
    }
};
