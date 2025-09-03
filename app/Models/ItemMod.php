<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemMod extends Model
{
    use HasFactory;

    protected $fillable = ['item_id','affix_id','tier_id','value','is_locked'];

    public function item(): BelongsTo {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function affix(): BelongsTo {
        return $this->belongsTo(Affix::class, 'affix_id');
    }

    public function tier(): BelongsTo {
        return $this->belongsTo(AffixTier::class, 'tier_id');
    }
}
