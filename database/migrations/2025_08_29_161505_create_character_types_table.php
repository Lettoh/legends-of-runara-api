<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('character_types', function (Blueprint $table) {
            $table->smallIncrements('id');   // 1,2,3…
            $table->string('name', 40)->unique();
        });

        Schema::table('characters', function (Blueprint $table) {
            $table->unsignedSmallInteger('type_id')->nullable()->change();
            $table->foreign('type_id')
                ->references('id')->on('character_types')
                ->nullOnDelete();
        });
    }

    public function down(): void {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropForeign(['type_id']);
        });
        Schema::dropIfExists('character_types');
    }
};
