<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    protected $fillable = [
        'user_id', 'resource_id', 'idle_run_id', 'enc_index',
        'delta', 'context', 'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }
    public function run(): BelongsTo
    {
        return $this->belongsTo(IdleRun::class, 'idle_run_id');
    }
}
