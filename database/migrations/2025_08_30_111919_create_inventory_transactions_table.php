<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $t) {
            $t->bigIncrements('id');

            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('resource_id');
            $t->unsignedBigInteger('idle_run_id')->nullable(); // run that produced it (or null)
            $t->unsignedSmallInteger('enc_index')->nullable(); // 1-based encounter number

            // positive to add, negative to remove
            $t->integer('delta');
            $t->string('context', 32)->default('idle_drop'); // e.g. idle_drop, craft, sell, admin
            $t->json('meta')->nullable();

            $t->timestamps();

            // Short, explicit names to avoid 64-char limit
            $t->foreign('user_id', 'inv_txn_user_fk')->references('id')->on('users')->onDelete('cascade');
            $t->foreign('resource_id', 'inv_txn_res_fk')->references('id')->on('resources')->onDelete('restrict');
            $t->foreign('idle_run_id', 'inv_txn_run_fk')->references('id')->on('idle_runs')->onDelete('cascade');

            // Prevent double-apply for the same (user, resource, run, encounter)
            $t->unique(['user_id','resource_id','idle_run_id','enc_index'], 'inv_txn_uniq');

            // Helpful secondary index for queries
            $t->index(['user_id','resource_id'], 'inv_txn_user_res_idx');
        });

        // (optional) enforce 1 row per (user,resource) in items table if not already present
        if (!Schema::hasColumn('inventory_items', 'is_locked')) {
            // ignore — you already have the table, this is just a note
        }
        Schema::table('inventory_items', function (Blueprint $t) {
            // Short name again
            $t->unique(['user_id','resource_id'], 'inv_item_user_res_uniq');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $t) {
            $t->dropUnique('inv_item_user_res_uniq');
        });
        Schema::table('inventory_transactions', function (Blueprint $t) {
            $t->dropForeign('inv_txn_user_fk');
            $t->dropForeign('inv_txn_res_fk');
            $t->dropForeign('inv_txn_run_fk');
            $t->dropUnique('inv_txn_uniq');
            $t->dropIndex('inv_txn_user_res_idx');
        });
        Schema::dropIfExists('inventory_transactions');
    }
};
