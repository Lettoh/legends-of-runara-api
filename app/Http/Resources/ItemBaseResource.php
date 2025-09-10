<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemBaseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'slot'             => new EquipmentSlotResource($this->whenLoaded('slot', $this->slot)),
            'ilvl_req'         => (int) $this->ilvl_req,
            'image_url'            => $this->image_url,

            'implicit_stat' => [
                'code' => $this->implicit_stat_code,
                'min'  => $this->implicit_min,
                'max'  => $this->implicit_max,
            ],

            'base_crit_chance' => (float) $this->base_crit_chance, // %
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
            'recipe' => $this->whenLoaded('recipeIngredients', function () {
                return $this->recipeIngredients->map(function ($r) {
                    return [
                        'resource_id' => (int) $r->id,
                        'name'        => $r->name,
                        'icon_url'    => $r->icon_url,
                        'quantity'    => (int) $r->pivot->quantity,
                    ];
                });
            }),

        ];
    }
}
