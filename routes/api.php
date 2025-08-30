<?php

use App\Http\Controllers\IdleRunController;
use App\Http\Controllers\MonsterController;
use App\Http\Controllers\MonsterResourceController;
use App\Http\Controllers\ResourceController;
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

    Route::get   ('/inventory/{user}',                 [InventoryController::class, 'index']);
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
});


Route::get('/users', [UserSearchController::class, 'index'])
    ->middleware('auth:sanctum', 'admin');

Route::get('/user/{id}', [UserController::class, 'retrieveUser'])
    ->middleware('auth:sanctum', 'admin');

Route::post('/zones/{zone}', [ZoneController::class, 'update'])
    ->middleware('auth:sanctum');
