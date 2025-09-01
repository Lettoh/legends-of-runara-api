<?php

// app/Http/Resources/ZoneResource.php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ZoneResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'         => (int) $this->id,
            'name'       => (string) $this->name,
            'description'=> $this->description,
            'min_level'  => (int) $this->min_level,
            'max_level'  => (int) $this->max_level,
            'image_url'  => $this->image_url,
            'player_can_access' => $this->canUserAccessZone($request->user(), (int) $this->min_level)
            // optionnel : nécessite ->withCount('monsters') sur la requête
            // 'monsters_count' => $this->when(isset($this->monsters_count), (int) $this->monsters_count),
        ];
    }

    protected function canUserAccessZone($user, $min_level): bool
    {
        $c = $user->characters()->first();
        if ($c->level < $min_level) return false;
        return true;
    }
}
