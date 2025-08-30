<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resource extends Model
{
    protected $fillable = ['name','description','icon','rarity','tradeable'];
    protected $appends = ['icon_url'];

    public function getIconUrlAttribute(): string {
        return $this->icon
            ? asset('storage/resources/'.$this->icon)
            : asset('storage/resources/default-resource.png');
    }

    public function monsters(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Monster::class, 'monster_resource')
            ->withPivot(['drop_chance','min_qty','max_qty'])
            ->withTimestamps();
    }

    public function inventoryItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }
}
