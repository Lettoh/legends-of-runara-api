<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Spell extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'description',
        'target',
        'base_power',
        'scaling_str',
        'scaling_pow',
        'cooldown_turns',
        'meta',
    ];

    protected $appends = ['image_url'];

    protected $casts = [
        'base_power'     => 'integer',
        'scaling_str'    => 'float',
        'scaling_pow'    => 'float',
        'cooldown_turns' => 'integer',
        'meta'           => 'array',
    ];

    public function getImageUrlAttribute(): string
    {
        if (!$this->image) {
            return asset('storage/spells/default-spell.png');
        }
        return asset('storage/spells/'.$this->image);
    }

    public function effects(): HasMany
    {
        return $this->hasMany(SpellStatusEffect::class);
    }

    public function characterTypes(): BelongsToMany
    {
        return $this->belongsToMany(CharacterType::class, 'character_type_spell')
            ->withPivot(['unlock_level', 'required_specialization'])
            ->withTimestamps();
    }
}
