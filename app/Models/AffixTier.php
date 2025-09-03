<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffixTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'affix_id','tier','min_value','max_value','item_level_min','item_level_max','weight',
    ];

    public function affix(): BelongsTo {
        return $this->belongsTo(Affix::class, 'affix_id');
    }
}
