<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CharacterTypeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('character_types')->upsert([
            ['id' => 1, 'name' => 'Guerrier'],
            ['id' => 2, 'name' => 'Mage'],
            ['id' => 3, 'name' => 'Archer'],
        ], ['id'], ['name']);
    }
}
