<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Affix extends Model
{
    use HasFactory;

    protected $fillable = [
        'code','name','stat_code','kind','effect','max_per_item',
    ];

    public function tiers(): HasMany {
        return $this->hasMany(AffixTier::class, 'affix_id');
    }

    public function slots(): BelongsToMany {
        return $this->belongsToMany(EquipmentSlot::class, 'affix_slot_rules', 'affix_id', 'slot_id');
    }
}
