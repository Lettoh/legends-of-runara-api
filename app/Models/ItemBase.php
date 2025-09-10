<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemBase extends Model
{
    use HasFactory;

    protected $fillable = [
        'slot_id','name','ilvl_req','image',
        'implicit_stat_code','implicit_min','implicit_max',
        'base_crit_chance',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset($this->image) : null;
    }

    public function slot(): BelongsTo {
        return $this->belongsTo(EquipmentSlot::class, 'slot_id');
    }

    public function items(): HasMany {
        return $this->hasMany(Item::class, 'base_id');
    }

    public function recipeIngredients(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Resource::class, 'item_base_recipe_ingredients', 'base_id', 'resource_id')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
