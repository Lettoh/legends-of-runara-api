<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EquipmentSlot extends Model
{
    use HasFactory;

    protected $fillable = ['code','name'];

    public function itemBases(): HasMany {
        return $this->hasMany(ItemBase::class, 'slot_id');
    }

    public function setCodeAttribute($v) { $this->attributes['code'] = strtolower($v); }
}
