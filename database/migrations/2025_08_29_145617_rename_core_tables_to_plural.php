<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /** Retourne le nom de contrainte FK (ou null) pour une colonne donnée. */
    private function fkName(string $table, string $column): ?string
    {
        $schema = DB::getDatabaseName();
        return DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', $schema)
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $column)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->value('CONSTRAINT_NAME');
    }

    /** Supprime la contrainte FK si elle existe. */
    private function dropFkIfExists(string $table, string $column): void
    {
        if (!Schema::hasTable($table)) return;
        if ($name = $this->fkName($table, $column)) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$name}`");
        }
    }

    public function up(): void
    {
        // 1) Drop FKs (peu importe leur nom)
        $this->dropFkIfExists('monster_zone', 'monster_id');
        $this->dropFkIfExists('monster_zone', 'zone_id');
        $this->dropFkIfExists('monster_resource', 'monster_id');
        $this->dropFkIfExists('monster_resource', 'resource_id');
        $this->dropFkIfExists('inventory_items', 'resource_id');

        // 2) Renommer les tables de base (si elles existent en singulier)
        if (Schema::hasTable('monster') && !Schema::hasTable('monsters')) {
            Schema::rename('monster', 'monsters');
        }
        if (Schema::hasTable('zone') && !Schema::hasTable('zones')) {
            Schema::rename('zone', 'zones');
        }
        if (Schema::hasTable('resource') && !Schema::hasTable('resources')) {
            Schema::rename('resource', 'resources');
        }

        // 3) Recréer les FKs vers les nouvelles tables
        if (Schema::hasTable('monster_zone')) {
            Schema::table('monster_zone', function (Blueprint $t) {
                // évite les doublons si elles existent déjà
                $t->foreign('monster_id')->references('id')->on('monsters')->cascadeOnDelete();
                $t->foreign('zone_id')->references('id')->on('zones')->cascadeOnDelete();
            });
        }
        if (Schema::hasTable('monster_resource')) {
            Schema::table('monster_resource', function (Blueprint $t) {
                $t->foreign('monster_id')->references('id')->on('monsters')->cascadeOnDelete();
                $t->foreign('resource_id')->references('id')->on('resources')->cascadeOnDelete();
            });
        }
        if (Schema::hasTable('inventory_items')) {
            Schema::table('inventory_items', function (Blueprint $t) {
                $t->foreign('resource_id')->references('id')->on('resources')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        // Drop FKs côté pluriel
        $this->dropFkIfExists('monster_zone', 'monster_id');
        $this->dropFkIfExists('monster_zone', 'zone_id');
        $this->dropFkIfExists('monster_resource', 'monster_id');
        $this->dropFkIfExists('monster_resource', 'resource_id');
        $this->dropFkIfExists('inventory_items', 'resource_id');

        // Renommer tables en singulier si besoin
        if (Schema::hasTable('monsters') && !Schema::hasTable('monster')) {
            Schema::rename('monsters', 'monster');
        }
        if (Schema::hasTable('zones') && !Schema::hasTable('zone')) {
            Schema::rename('zones', 'zone');
        }
        if (Schema::hasTable('resources') && !Schema::hasTable('resource')) {
            Schema::rename('resources', 'resource');
        }

        // Recréer FKs vers singulier
        if (Schema::hasTable('monster_zone')) {
            Schema::table('monster_zone', function (Blueprint $t) {
                $t->foreign('monster_id')->references('id')->on('monster')->cascadeOnDelete();
                $t->foreign('zone_id')->references('id')->on('zone')->cascadeOnDelete();
            });
        }
        if (Schema::hasTable('monster_resource')) {
            Schema::table('monster_resource', function (Blueprint $t) {
                $t->foreign('monster_id')->references('id')->on('monster')->cascadeOnDelete();
                $t->foreign('resource_id')->references('id')->on('resource')->cascadeOnDelete();
            });
        }
        if (Schema::hasTable('inventory_items')) {
            Schema::table('inventory_items', function (Blueprint $t) {
                $t->foreign('resource_id')->references('id')->on('resource')->cascadeOnDelete();
            });
        }
    }
};
