<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Character extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
        'level',
        'type_id',
        'ascendancy_type_id',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(CharacterType::class, 'type_id');
    }

    public function getPortraitUrlAttribute(): string
    {
        if (!empty($this->attributes['image_url'])) {
            return $this->attributes['image_url'];
        }

        // si le type a une image
        if ($this->relationLoaded('type') && !empty($this->type->image_url)) {
            return $this->type->image_url;
        }

        // fallback par type_id -> fichier public/
        // Mets 3 fichiers dans public/images/portraits/ : warrior.png, mage.png, archer.png
        $map = [
            1 => asset('images/portraits/warrior.png'), // Guerrier
            2 => asset('images/portraits/mage.png'),    // Mage
            3 => asset('images/portraits/archer.png'),  // Archer
        ];

        return $map[$this->type_id] ?? asset('images/portraits/default.png');
    }
}
