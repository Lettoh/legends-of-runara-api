<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IdleRun extends Model
{
    protected $fillable = [
        'user_id','zone_id','duration_sec','start_at','end_at','status','seed',
        'encounters_total','encounters_done','interval_sec',
        'gold_earned','xp_earned','team_snapshot','loot_summary',
    ];

    protected $casts = [
        'team_snapshot'    => 'array',
        'start_at'         => 'datetime',
        'end_at'           => 'datetime',
        'duration_sec'     => 'integer',
        'encounters_total' => 'integer',
        'encounters_done'  => 'integer',
        'interval_sec'     => 'integer',
        'gold_earned'      => 'integer',
        'xp_earned'        => 'integer',
        'seed'             => 'integer',
    ];

    /** La zone dans laquelle l’exploration a lieu */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    /** Le joueur qui fait l’exploration */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Compteurs de rencontres par monstre
    public function monsterCounters(): HasMany
    {
        return $this->hasMany(IdleRunMonster::class);
    }

    // Lignes de butin cumulées
    public function lootRows(): HasMany
    {
        return $this->hasMany(IdleRunLoot::class);
    }
}
