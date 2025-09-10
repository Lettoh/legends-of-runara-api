<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => (string) $this->id,
            'rarity'      => $this->rarity,
            'item_level'  => (int) $this->item_level,
            'base'        => new ItemBaseResource($this->whenLoaded('base', $this->base)),
            'implicit'    => [
                'stat_code' => $this->base?->implicit_stat_code,
                'value'     => $this->implicit_value,
            ],
            'mods'        => $this->whenLoaded('mods', function () {
                return $this->mods->map(function ($m) {
                    return [
                        'id'      => $m->id,
                        'code'    => $m->affix?->code,
                        'name'    => $m->affix?->name,
                        'effect'  => $m->affix?->effect,     // add|percent
                        'stat'    => $m->affix?->stat_code,  // strength|power|...
                        'tier'    => $m->tier?->tier,
                        'value'   => $m->value,
                        'locked'  => (bool) $m->is_locked,
                    ];
                });
            }),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}
