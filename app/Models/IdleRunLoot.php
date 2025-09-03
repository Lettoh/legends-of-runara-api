<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdleRunLoot extends Model
{
    protected $table = 'idle_run_loot';
    public $timestamps = false;

    protected $fillable = ['idle_run_id','resource_id','qty'];

    public function idleRun(): BelongsTo
    {
        return $this->belongsTo(IdleRun::class);
    }
    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }
}
