<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SpellResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'image_url'      => $this->image_url,
            'description'    => $this->description,
            'target'         => $this->target,
            'base_power'     => $this->base_power,
            'scaling_str'    => $this->scaling_str,
            'scaling_pow'    => $this->scaling_pow,
            'cooldown_turns' => $this->cooldown_turns,
            'meta'           => $this->meta,
            'effects'        => SpellStatusEffectResource::collection($this->whenLoaded('effects')),
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}
