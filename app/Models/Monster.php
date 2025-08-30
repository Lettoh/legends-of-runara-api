<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Monster extends Model
{
    protected $fillable = ['name','level','hp','atk','def', 'image'];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): string
    {
        if (!$this->image) {
            return asset('storage/monsters/default-monster.png');
        }
        return asset('storage/monsters/'.$this->image);
    }

    public function zones(): BelongsToMany
    {
        return $this->belongsToMany(Zone::class, 'monster_zone')
            ->withPivot('spawn_chance')
            ->withTimestamps();
    }

    public function resources(): BelongsToMany
    {
        return $this->belongsToMany(Resource::class, 'monster_resource')
            ->withPivot(['drop_chance','min_qty','max_qty'])
            ->withTimestamps();
    }
}
