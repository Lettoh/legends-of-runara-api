<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
    use HasFactory;

    protected $fillable = ['name','description','min_level', 'max_level','image'];
    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): string
    {
        if (!$this->image) {
            return asset('storage/zones/default-zone.png');
        }
        return asset('storage/zones/'.$this->image);
    }

    public function monsters() {
        return $this->belongsToMany(Monster::class, 'monster_zone')
            ->withPivot('spawn_chance')
            ->withTimestamps();
    }

    public function idleRuns(): HasMany
    {
        return $this->hasMany(IdleRun::class);
    }
}
