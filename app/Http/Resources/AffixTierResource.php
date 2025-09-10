<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AffixTierResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'tier'            => (int) $this->tier,
            'min_value'       => (int) $this->min_value,
            'max_value'       => (int) $this->max_value,
            'item_level_min'  => (int) $this->item_level_min,
            'item_level_max'  => (int) $this->item_level_max,
            'weight'          => (int) $this->weight,
        ];
    }
}
