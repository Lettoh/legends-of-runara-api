<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class CharacterType extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
    ];

    public function characters(): HasMany
    {
        return $this->hasMany(Character::class, 'type_id');
    }

    public function spells(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Spell::class, 'character_type_spell')
            ->withPivot(['unlock_level','required_specialization'])
            ->withTimestamps();
    }
}
