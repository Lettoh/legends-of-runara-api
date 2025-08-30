<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdleRunMonster extends Model
{
    protected $table = 'idle_run_monsters';
    public $timestamps = false;

    protected $fillable = ['idle_run_id','monster_id','count','last_at'];
    protected $casts = ['last_at' => 'datetime'];

    public function idleRun(): BelongsTo
    {
        return $this->belongsTo(IdleRun::class);
    }
    public function monster(): BelongsTo
    {
        return $this->belongsTo(Monster::class);
    }
}
