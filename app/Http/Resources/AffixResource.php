<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AffixResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'code'          => $this->code,
            'name'          => $this->name,
            'stat_code'     => $this->stat_code,
            'kind'          => $this->kind,
            'effect'        => $this->effect,
            'max_per_item'  => (int) $this->max_per_item,

            'slots'         => EquipmentSlotResource::collection($this->whenLoaded('slots', $this->slots)),
            'tiers'         => AffixTierResource::collection($this->whenLoaded('tiers', $this->tiers)),
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
