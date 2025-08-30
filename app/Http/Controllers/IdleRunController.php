<?php

// app/Http/Controllers/IdleRunController.php
namespace App\Http\Controllers;

use App\Jobs\ProcessIdleRun;
use App\Models\IdleRun;
use App\Models\IdleRunLoot;
use App\Models\IdleRunMonster;
use App\Models\InventoryItem;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IdleRunController extends Controller
{
    public function start(Request $req)
    {
        $user = $req->user();
        $data = $req->validate([
            'zone_id' => ['required','exists:zones,id'],
            'minutes' => ['required','integer','min:1','max:1440'],
        ]);

        $max = $user->is_premium ? 240 : 120; // ajuste si besoin
        $minutes = min($data['minutes'], $max);

        $zone = Zone::with(['monsters' => function($q){
            $q->select('monsters.id','monsters.level');
        }])->findOrFail($data['zone_id']);

        // Snapshot équipe (adapte à ton modèle)
        $team = $user->characters()->select('id','name','level','hp','strength','power', 'defense', 'type_id')->get();
        $teamSnapshot = $team->map(fn($c) => $c->toArray());

        // Compute power & nombre de rencontres
        $encounters = max(1, $this->computeEncounterCount($team, $zone, $minutes));
        $interval = max(3, intdiv($minutes * 60, $encounters));

        $run = IdleRun::create([
            'user_id'          => $user->id,
            'zone_id'          => $zone->id,
            'team_snapshot'    => $teamSnapshot,
            'duration_sec'     => $minutes * 60,
            'start_at'         => now(),
            'end_at'           => now()->addMinutes($minutes),
            'status'           => 'running',
            'seed'             => random_int(1, PHP_INT_MAX),
            'encounters_total' => $encounters,
            'encounters_done'  => 0,
            'interval_sec'     => $interval,
            'gold_earned'      => 0,
            'xp_earned'        => 0,
        ]);

        $firstDelay = (int) $run->interval_sec;

        try {
            ProcessIdleRun::dispatch($run->id)
                ->onQueue('idle')
                ->delay(now()->addSeconds($firstDelay));
        } catch (\Throwable $e) {
            Log::error('IdleRun enqueue failed', ['run_id' => $run->id, 'err' => $e->getMessage()]);
        }

        $run = $run->fresh(); // on relit depuis la base

        return response()->json([
            'data' => [
                'id'               => $run->id,
                'user_id'          => $run->user_id,
                'zone_id'          => $run->zone_id,
                'duration_sec'     => (int) $run->duration_sec,
                'start_at'         => optional($run->start_at)->toIso8601String(),
                'end_at'           => optional($run->end_at)->toIso8601String(),
                'status'           => (string) $run->status,
                'encounters_total' => (int) $run->encounters_total,
                'encounters_done'  => (int) $run->encounters_done,
                'interval_sec'     => (int) $run->interval_sec,
                'gold_earned'      => (int) $run->gold_earned,
                'xp_earned'        => (int) $run->xp_earned,
            ]
        ], 201);
    }

    public function active(Request $request)
    {
        $user = $request->user();

        $run = IdleRun::with(['zone:id,name,image']) // ajoute les relations utiles
        ->where('user_id', $user->id)
            ->where('status', 'running')
            ->latest('id')
            ->first();

        // Le composable accepte {data: ...} ou null
        return response()->json(['data' => $run]);
    }

    public function show($id)
    {
        $run = IdleRun::with([
            'zone:id,name,image,min_level,max_level',
            'monsterCounters' => fn ($q) => $q
                ->with(['monster:id,name,image'])
                ->orderByDesc('count'),
            'lootRows' => fn ($q) => $q
                ->with(['resource:id,name,icon'])
                ->orderByDesc('qty'),
        ])->findOrFail($id);

        return response()->json([
            'id'               => $run->id,
            'zone_id'          => $run->zone_id,
            'status'           => $run->status,
            'start_at'         => $run->start_at,
            'end_at'           => $run->end_at,
            'duration_sec'     => $run->duration_sec,
            'encounters_total' => $run->encounters_total,
            'encounters_done'  => $run->encounters_done,
            'interval_sec'     => $run->interval_sec,
            'gold_earned'      => $run->gold_earned,
            'xp_earned'        => $run->xp_earned,
            'zone' => $run->zone ? [
                'id'         => $run->zone->id,
                'name'       => $run->zone->name,
                'image_url'  => $run->zone->image_url,
                'min_level'  => $run->zone->min_level,
                'max_level'  => $run->zone->max_level,
            ] : null,
            // === ce que Idling.vue lit ===
            'encounters' => $run->monsterCounters->map(fn ($m) => [
                'monster_id' => $m->monster_id,
                'name'       => $m->monster?->name,
                'image_url'  => $m->monster?->image_url,
                'count'      => (int) $m->count,
            ])->values(),
            'loot' => $run->lootRows->map(fn ($l) => [
                'resource_id' => $l->resource_id,
                'name'        => $l->resource?->name,
                'icon_url'    => $l->resource?->icon_url,
                'qty'         => (int) $l->qty,
            ])->values(),
        ]);
    }


    public function stop(Request $req, IdleRun $run)
    {
        abort_unless($run->user_id === $req->user()->id, 403);
        if ($run->status === 'running') {
            $run->status = 'finished';
            $run->end_at = now();
            $run->save();
        }
        return response()->json(['run' => $this->serializeRun($run->fresh())]);
    }

    public function latestUnclaimed(Request $req) {
        $run = IdleRun::where('user_id', $req->user()->id)
            ->where('status', 'finished')
            ->latest('end_at')
            ->first();

        return response()->json(['data' => $run ? [
            'id' => $run->id,
            'zone_id' => $run->zone_id,
            'status' => $run->status,
        ] : null]);
    }

    public function claim(IdleRun $run, Request $req) {
        abort_unless($run->user_id === $req->user()->id, 403);
        if ($run->status === 'finished') {
            $run->update(['status' => 'claimed']);
        }
        return response()->json(['data' => ['id' => $run->id, 'status' => $run->status]]);
    }



    /** --- Helpers --- */

    private function computeEncounterCount($team, Zone $zone, int $minutes): int
    {
        $avgLvl   = max(1, (int)round($team->avg('level') ?: 1));
        $rangeMid = max(1, (int)round(($zone->min_level + $zone->max_level) / 2));
        $power    = $team->sum(function ($c) {
            $base = ($c->hp ?? 0)*0.25 + ($c->defense ?? 0)*0.8;
            $base += ($c->strength ?? 0)*1.3 + ($c->power ?? 0)*1.3;
            // Petit biais par classe si tu veux (type_id: 1=guerrier, 2=mage, 3=archer…)
            if ($c->type_id == 1) $base *= 1.05;
            if ($c->type_id == 2) $base *= 1.05;
            return $base;
        });
        $diffCoef = max(0.6, min(1.6, 1 + ($avgLvl - $rangeMid) * 0.05));
        $basePerMin = 1.5; // moyenne ~1.5 rencontres / min
        return max(1, (int)floor($minutes * $basePerMin * $diffCoef * (1 + $power/5000)));
    }

    private function serializeRun(IdleRun $run): array
    {
        $now  = now();
        $eta  = $run->end_at?->diffInSeconds($now, false);
        $left = max(0, -$eta);

        return [
            'id' => $run->id,
            'status' => $run->status,
            'zone_id' => $run->zone_id,
            'encounters_done' => $run->encounters_done,
            'encounters_total'=> $run->encounters_total,
            'interval_sec' => (int)$run->interval_sec,
            'gold_earned'  => (int)$run->gold_earned,
            'xp_earned'    => (int)$run->xp_earned,
            'start_at'     => $run->start_at,
            'end_at'       => $run->end_at,
            'remaining_sec'=> $left,
        ];
    }
}

