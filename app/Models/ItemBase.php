<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemBase extends Model
{
    use HasFactory;

    protected $fillable = [
        'slot_id','name','ilvl_req','image',
        'implicit_stat_code','implicit_min','implicit_max',
        'base_crit_chance',
    ];

    public function slot(): BelongsTo {
        return $this->belongsTo(EquipmentSlot::class, 'slot_id');
    }

    public function items(): HasMany {
        return $this->hasMany(Item::class, 'base_id');
    }
}
