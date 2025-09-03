<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id','base_id','owner_user_id','item_level','rarity','implicit_value'];

    public function base(): BelongsTo {
        return $this->belongsTo(ItemBase::class, 'base_id');
    }

    public function owner(): BelongsTo {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function mods(): HasMany {
        return $this->hasMany(ItemMod::class, 'item_id');
    }
}
