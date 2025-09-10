<?php

namespace App\Jobs;

use App\Events\IdleRunFinished;
use App\Events\IdleRunTicked;
use App\Events\CharacterLeveledUp;          // <= NEW (event pour le watcher de stats)
use App\Models\IdleRun;
use App\Models\IdleRunLoot;
use App\Models\IdleRunMonster;
use App\Models\Monster;
use App\Models\Zone;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Character;
use App\Support\Leveling;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessIdleRun implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $idleRunId) {}

    public function handle(): void
    {
        // ===== 1) TICK ATOMIQUE =====
        $result = DB::transaction(function () {
            /** @var IdleRun|null $run */
            $run = IdleRun::query()->lockForUpdate()->find($this->idleRunId);
            if (! $run || $run->status !== 'running') {
                return null; // rien à faire
            }

            // Fin naturelle ?
            if ($run->encounters_done >= $run->encounters_total) {
                $run->status = 'finished';
                $run->save();

                return $run;
            }

            // ===== 2) Pool de monstres =====
            /** @var Zone $zone */
            $zone = Zone::query()
                ->with(['monsters' => function ($q) {
                    $q->select(
                        'monsters.id',
                        'monsters.name',
                        'monsters.level',
                        'monsters.gold_min',
                        'monsters.gold_max'
                    );
                }])
                ->findOrFail($run->zone_id);

            $pool = collect($zone->monsters)
                ->map(fn ($m) => [
                    'id'     => (int) $m->id,
                    'name'   => (string) $m->name,
                    'lvl'    => (int) $m->level,
                    'chance' => (float) ($m->pivot->spawn_chance ?? 0.0),
                    'gmin'   => (int) ($m->gold_min ?? 0),
                    'gmax'   => (int) ($m->gold_max ?? 0),
                ])
                ->filter(fn ($x) => $x['chance'] > 0)
                ->values();

            // ===== 3) Tirage indépendant =====
            $encIndex = (int) $run->encounters_done;             // 0-based
            $seed     = (int) $run->seed ^ ($encIndex * 10007);  // RNG déterministe
            mt_srand($seed);

            $spawned = [];
            foreach ($pool as $m) {
                $roll = mt_rand(0, 10000) / 100.0; // 0..100
                if ($roll <= $m['chance']) {
                    $spawned[] = $m;
                }
            }

            if (empty($spawned) && $pool->isNotEmpty()) {
                $spawned[] = $pool->sortByDesc('chance')->first();
            }

            if (empty($spawned)) {
                $run->encounters_done++;
                if ($run->encounters_done >= $run->encounters_total) {
                    $run->status = 'finished';
                }
                $run->save();

                return $run;
            }

            // ===== 4) Charger les drops =====
            $monsterIds = array_column($spawned, 'id');
            $monsters = Monster::query()
                ->with(['resources' => function ($q) {
                    $q->select(
                        'resources.id',
                        'resources.name',
                        'resources.icon',
                        'monster_resource.drop_chance',
                        'monster_resource.min_qty',
                        'monster_resource.max_qty'
                    );
                }])
                ->whereIn('id', $monsterIds)
                ->get()
                ->keyBy('id');

            // ===== 5) Gains & compteurs =====
            $goldGain = 0;
            $xpGain   = 0;

            foreach ($spawned as $sm) {
                // Compteur d'apparition
                IdleRunMonster::query()->updateOrCreate(
                    ['idle_run_id' => $run->id, 'monster_id' => $sm['id']],
                    ['last_at' => now()]
                );
                IdleRunMonster::query()
                    ->where(['idle_run_id' => $run->id, 'monster_id' => $sm['id']])
                    ->update(['count' => DB::raw('count + 1')]);

                // Or
                if ($sm['gmax'] > 0) {
                    $min = max(0, $sm['gmin']);
                    $max = max($min, $sm['gmax']);
                    $goldGain += mt_rand($min, $max);
                }

                // XP (par rencontre)
                $xpGain += $this->xpForMonster($sm['lvl']);

                // Drops
                $full = $monsters->get($sm['id']);
                if ($full) {
                    foreach ($full->resources as $r) {
                        $chance = (float) $r->pivot->drop_chance; // 0..100
                        if ($chance <= 0) continue;

                        $roll = mt_rand(0, 10000) / 100.0;
                        if ($roll <= $chance) {
                            $min = (int) $r->pivot->min_qty;
                            $max = (int) $r->pivot->max_qty;
                            if ($max < $min) $max = $min;

                            $qty = max(0, mt_rand($min, $max));

                            // Récap “run” (pour l’UI)
                            $lootRow = IdleRunLoot::query()->firstOrCreate(
                                ['idle_run_id' => $run->id, 'resource_id' => $r->id],
                                ['qty' => 0]
                            );
                            $lootRow->increment('qty', $qty);

                            // 1) Transaction idempotente par rencontre
                            $encNo = $run->encounters_done + 1; // 1-based
                            DB::table('inventory_transactions')->insertOrIgnore([
                                'user_id'     => $run->user_id,
                                'resource_id' => $r->id,
                                'idle_run_id' => $run->id,
                                'enc_index'   => $encNo,
                                'delta'       => $qty,
                                'context'     => 'idle_drop',
                                'meta'        => json_encode(['monster_id' => $sm['id']]),
                                'created_at'  => now(),
                                'updated_at'  => now(),
                            ]);

                            // 2) Stock visible
                            $item = InventoryItem::query()
                                ->lockForUpdate()
                                ->firstOrCreate(
                                    ['user_id' => $run->user_id, 'resource_id' => $r->id],
                                    ['quantity' => 0, 'is_locked' => false]
                                );
                            $item->increment('quantity', $qty);
                        }
                    }
                }
            }

            $give_xp = true;

            // ===== 5.bis) Donner l’XP EN ENTIER à chaque membre =====
            $teamIds = collect($run->team_snapshot)->pluck('id')->filter()->all();
            if (!empty($teamIds) && $xpGain > 0) {
                // Lock des persos pour éviter les courses
                $team = Character::query()
                    ->whereIn('id', $teamIds)
                    ->lockForUpdate()
                    ->get();

                foreach ($team as $ch) {
                    $before = (int) $ch->level;

                    // Ne pas donner d'xp si le level dépasse le max level de la zone
                    if ($ch->level >= $zone->max_level) {
                        $give_xp = false;
                        break;
                    }

                    // Chaque perso reçoit 100% de l’XP du tick
                    $res = Leveling::applyGain($ch, $xpGain);
                    $ch->save();

                    $after = (int) $ch->level;

                    // Si level-up => event pour que le listener augmente les stats
                    if ($after > $before) {
                        DB::afterCommit(function () use ($ch, $before, $after) {
                            try {
                                event(new CharacterLeveledUp($ch->fresh(), $before, $after, $after - $before));
                            } catch (\Throwable $e) {
                                Log::warning('CharacterLeveledUp dispatch failed', [
                                    'char_id' => $ch->id,
                                    'error'   => $e->getMessage(),
                                ]);
                            }
                        });
                    }
                }
            }

            // ===== 6) Avancer l’état du run =====
            $run->encounters_done++;
            $run->gold_earned += $goldGain;

            // IMPORTANT : on stocke l’XP “par personnage” pour matcher l’UI que si on a donné de l'xp
            if ($give_xp) $run->xp_earned   += $xpGain;

            if ($run->encounters_done >= $run->encounters_total) {
                $run->status = 'finished';
            }

            $run->save();

            return $run;
        });

        if (! $result instanceof IdleRun) {
            return;
        }

        $run = $result;

        // ===== 7) Re-planifier =====
        if ($run->status === 'running' && $run->encounters_done < $run->encounters_total) {
            $delay = $this->nextDelaySeconds($run);
            self::dispatch($run->id)
                ->onQueue('idle')
                ->delay(now()->addSeconds($delay));
        }

        // ===== 8) Broadcast APRÈS COMMIT =====
        try {
            $payload = [
                'id'               => $run->id,
                'status'           => $run->status,
                'encounters_done'  => $run->encounters_done,
                'encounters_total' => $run->encounters_total,
                'interval_sec'     => (int) $run->interval_sec,
                'start_at'         => $run->start_at,
                'duration_sec'     => (int) $run->duration_sec,
                'zone_id'          => $run->zone_id,
            ];

            broadcast(new IdleRunTicked($payload));

            if ($run->status !== 'running') {
                broadcast(new IdleRunFinished($payload));
            }
        } catch (\Throwable $e) {
            Log::warning('IdleRun broadcast failed', [
                'run_id' => $run->id,
                'error'  => $e->getMessage(),
            ]);
        }
    }

    /** Prochain délai (en s). */
    private function nextDelaySeconds(IdleRun $run): int
    {
        $interval = (int) $run->interval_sec;
        $targetTs = $run->start_at->getTimestamp()
            + (($run->encounters_done + 1) * $interval);
        $nowTs    = now()->getTimestamp();
        $delay    = $targetTs - $nowTs;

        return $delay > 1 ? $delay : 1;
    }

    private function xpForMonster(int $lvl): int
    {
        return max(1, (int) round(5 + $lvl * 1.5));
    }
}
