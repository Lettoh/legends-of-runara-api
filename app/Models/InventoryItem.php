<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $fillable = ['user_id', 'resource_id', 'quantity', 'is_locked'];

    public function user()    { return $this->belongsTo(User::class); }
    public function resource(){ return $this->belongsTo(Resource::class); }
}
