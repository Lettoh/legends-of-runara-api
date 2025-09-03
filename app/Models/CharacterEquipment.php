<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharacterEquipment extends Model
{
    use HasFactory;

    protected $table = 'character_equipment';

    protected $fillable = ['character_id','item_id','slot_id','equipped_at'];

    public function character(): BelongsTo {
        return $this->belongsTo(Character::class, 'character_id');
    }

    public function item(): BelongsTo {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function slot(): BelongsTo {
        return $this->belongsTo(EquipmentSlot::class, 'slot_id');
    }
}
