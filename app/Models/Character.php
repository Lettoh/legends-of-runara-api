<?php

namespace App\Models;

use App\Support\Leveling;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

class Character extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
        'level',
        'type_id',
        'ascendancy_type_id',
        'user_id',
    ];

    protected $appends = ['xp_to_next', 'xp_percent'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(CharacterType::class, 'type_id');
    }

    public function getSpellsAttribute(): Collection
    {
        $this->loadMissing('type.spells');

        $spells = $this->type?->spells ?? collect();

        return $spells->values();
    }

    public function getUnlockedSpellsAttribute(): Collection
    {
        $this->loadMissing('type.spells');

        $spells = $this->type?->spells ?? collect();

        $level = (int) $this->level;
        $asc   = $this->ascendancy_type_id; // peut être null

        return $spells->filter(function ($spell) use ($level, $asc) {
            $unlock = (int) ($spell->pivot->unlock_level ?? PHP_INT_MAX);
            if ($unlock > $level) return false;

            $req = $spell->pivot->required_specialization ?? null;
            return $req === null || (string) $req === (string) $asc;
        })->values();
    }

    public function getPortraitUrlAttribute(): string
    {
        if (!empty($this->attributes['image_url'])) {
            return $this->attributes['image_url'];
        }

        // si le type a une image
        if ($this->relationLoaded('type') && !empty($this->type->image_url)) {
            return $this->type->image_url;
        }

        // fallback par type_id -> fichier public/
        $map = [
            1 => asset('images/portraits/warrior.png'), // Guerrier
            2 => asset('images/portraits/mage.png'),    // Mage
            3 => asset('images/portraits/archer.png'),  // Archer
        ];

        return $map[$this->type_id] ?? asset('images/portraits/default.png');
    }

    public function gainXp(int $amount): array
    {
        return Leveling::applyGain($this, $amount);
    }

    public function getXpToNextAttribute(): int
    {
        return Leveling::xpToNext($this);
    }

    public function getXpPercentAttribute(): float
    {
        $need = $this->xp_to_next;
        return $need > 0 ? round(100 * ((int)$this->xp) / $need, 2) : 100.0;
    }
}
