<?php

use App\Http\Controllers\AffixController;
use App\Http\Controllers\CharacterEquipmentController;
use App\Http\Controllers\CharacterTypeController;
use App\Http\Controllers\CharacterTypeSpellController;
use App\Http\Controllers\CraftController;
use App\Http\Controllers\EquipmentSlotController;
use App\Http\Controllers\IdleRunController;
use App\Http\Controllers\ItemBaseController;
use App\Http\Controllers\MonsterController;
use App\Http\Controllers\MonsterResourceController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\SpellController;
use App\Http\Controllers\SpellStatusEffectController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserSearchController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\ZoneMonsterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/team/me', [TeamController::class, 'me']);
    Route::get('/team/{user_id}', [TeamController::class, 'index']);

    Route::get('/inventory/{user}', [InventoryController::class, 'index']);

    Route::get('/zones', [ZoneController::class, 'index']);
    Route::get('/zones/{zone}', [ZoneController::class, 'show']);
    Route::post('/zones', [ZoneController::class, 'store'])->middleware('admin');
    Route::post('/zones/{zone}', [ZoneController::class, 'update'])->middleware('admin');
    Route::delete('/zones/{zone}', [ZoneController::class, 'destroy'])->middleware('admin');

    Route::get   ('/zones/{zone}/monsters', [ZoneMonsterController::class, 'index']);
    Route::post  ('/zones/{zone}/monsters', [ZoneMonsterController::class, 'store'])->middleware('admin');
    Route::delete('/zones/{zone}/monsters/{monster}', [ZoneMonsterController::class, 'destroy'])->middleware('admin');
    Route::patch('/zones/{zone}/monsters/{monster}', [ZoneMonsterController::class, 'update'])->middleware('admin');

    Route::get   ('/monsters',            [MonsterController::class, 'index']);
    Route::post  ('/monsters',            [MonsterController::class, 'store'])->middleware('admin');
    Route::patch ('/monsters/{monster}',  [MonsterController::class, 'update'])->middleware('admin');
    Route::delete('/monsters/{monster}',  [MonsterController::class, 'destroy'])->middleware('admin');

    Route::get   ('/resources',           [ResourceController::class, 'index']);
    Route::post  ('/resources',           [ResourceController::class, 'store'])->middleware('admin');
    Route::patch ('/resources/{resource}',[ResourceController::class, 'update'])->middleware('admin');
    Route::delete('/resources/{resource}',[ResourceController::class, 'destroy'])->middleware('admin');

    Route::get   ('/monsters/{monster}/resources',                    [MonsterResourceController::class, 'index']);
    Route::post  ('/monsters/{monster}/resources',                    [MonsterResourceController::class, 'store'])->middleware('admin');
    Route::patch ('/monsters/{monster}/resources/{resource}',         [MonsterResourceController::class, 'update'])->middleware('admin');
    Route::delete('/monsters/{monster}/resources/{resource}',         [MonsterResourceController::class, 'destroy'])->middleware('admin');

    Route::get   ('/inventory/{user}',                 [InventoryController::class, 'index'])->middleware('inventory');
    Route::post  ('/inventory/{user}/add',             [InventoryController::class, 'add'])->middleware('admin');
    Route::post  ('/inventory/{user}/consume',         [InventoryController::class, 'consume'])->middleware('admin');
    Route::patch ('/inventory/{user}/set',             [InventoryController::class, 'set'])->middleware('admin');
    Route::delete('/inventory/{user}/{resource}',      [InventoryController::class, 'destroy'])->middleware('admin');

    Route::get('/idle/runs/active', [IdleRunController::class, 'active']);
    Route::get('/idle/runs/unclaimed', [IdleRunController::class, 'latestUnclaimed']);
    Route::post('/idle/runs',           [IdleRunController::class, 'start']);
    Route::get ('/idle/runs/{run}',     [IdleRunController::class, 'show']);
    Route::post('/idle/runs/{run}/stop',[IdleRunController::class, 'stop']);
    Route::post('/idle/runs/{run}/claim',[IdleRunController::class, 'claim']);

    Route::get('/spells', [SpellController::class, 'index']);
    Route::get('/spells/{spell}', [SpellController::class, 'show']);
    Route::post('/spells', [SpellController::class, 'store'])->middleware('admin');
    Route::put('/spells/{spell}', [SpellController::class, 'update'])->middleware('admin');
    Route::patch('/spells/{spell}', [SpellController::class, 'update'])->middleware('admin');
    Route::delete('/spells/{spell}', [SpellController::class, 'destroy'])->middleware('admin');

    Route::post('/spells/{spell}/effects', [SpellStatusEffectController::class, 'store'])->middleware('admin');
    Route::put('/spells/{spell}/effects/{effect}', [SpellStatusEffectController::class, 'update'])->middleware('admin');
    Route::patch('/spells/{spell}/effects/{effect}', [SpellStatusEffectController::class, 'update'])->middleware('admin');
    Route::delete('/spells/{spell}/effects/{effect}', [SpellStatusEffectController::class, 'destroy'])->middleware('admin');

    // List classes
    Route::get('/character-types', [CharacterTypeController::class, 'index']);
    // Spells <-> Class pivot
    Route::get('/character-types/{type}/spells', [CharacterTypeSpellController::class, 'index']);
    Route::post('/character-types/{type}/spells/{spell}', [CharacterTypeSpellController::class, 'store']);
    Route::patch('/character-types/{type}/spells/{spell}', [CharacterTypeSpellController::class, 'update']);
    Route::delete('/character-types/{type}/spells/{spell}', [CharacterTypeSpellController::class, 'destroy']);

    Route::post('/characters/{character}/equip', [CharacterEquipmentController::class, 'equip']);
    Route::delete('/characters/{character}/unequip/{slot}', [CharacterEquipmentController::class, 'unequip']);

    Route::get('/equipment-slots', [EquipmentSlotController::class, 'index']);
    Route::post('/equipment-slots',   [EquipmentSlotController::class, 'store']);
    Route::put('/equipment-slots/{slot}',    [EquipmentSlotController::class, 'update']);
    Route::delete('/equipment-slots/{slot}', [EquipmentSlotController::class, 'destroy']);

    // Item bases
    Route::get('/item-bases',        [ItemBaseController::class, 'index']);
    Route::get('/item-bases/{id}',   [ItemBaseController::class, 'show']);
    Route::post('/item-bases',       [ItemBaseController::class, 'store']);
    Route::put('/item-bases/{id}',   [ItemBaseController::class, 'update']);
    Route::delete('/item-bases/{id}',[ItemBaseController::class, 'destroy']);

    // Affixes (+ tiers + slot rules)
    Route::get('/affixes',         [AffixController::class, 'index']);
    Route::get('/affixes/{id}',    [AffixController::class, 'show']);
    Route::post('/affixes',        [AffixController::class, 'store']);
    Route::put('/affixes/{id}',    [AffixController::class, 'update']);
    Route::delete('/affixes/{id}', [AffixController::class, 'destroy']);

    // Recettes
    Route::get('/item-bases/{id}/recipe', [ItemBaseController::class, 'recipe']);
    Route::put('/item-bases/{id}/recipe', [ItemBaseController::class, 'updateRecipe'])->middleware('admin');

    // Craft
    Route::post('/craft', [CraftController::class, 'craft']);
});


Route::get('/users', [UserSearchController::class, 'index'])
    ->middleware('auth:sanctum', 'admin');

Route::get('/user/{id}', [UserController::class, 'retrieveUser'])
    ->middleware('auth:sanctum', 'admin');

Route::post('/zones/{zone}', [ZoneController::class, 'update'])
    ->middleware('auth:sanctum');
