<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SpellStatusEffectResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'kind'           => $this->kind,
            'mode'           => $this->mode,
            'value'          => $this->value,
            'vs'             => $this->vs,
            'duration_turns' => $this->duration_turns,
            'chance'         => $this->chance,
        ];
    }
}
