<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpellStatusEffect extends Model
{
    use HasFactory;

    protected $fillable = [
        'spell_id',
        'kind',           // defense|damage|elemental_weakness|stun|shield|dot
        'mode',           // percent|flat
        'value',          // signed integer; nullable for 'stun'
        'vs',             // strength|power for elemental_weakness
        'duration_turns', // tinyint
        'chance',         // 0..100
    ];

    protected $casts = [
        'value'          => 'integer',
        'duration_turns' => 'integer',
        'chance'         => 'integer',
    ];

    public function spell(): BelongsTo
    {
        return $this->belongsTo(Spell::class);
    }
}
