<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\SpellResource;

class CharacterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'level'       => (int) $this->level,
            'xp'           => (int) $this->xp,
            'xp_to_next'   => (int) $this->xp_to_next,
            'xp_percent'   => (float) $this->xp_percent,
            'type_id'     => (int) $this->type_id,
            'ascendancy_type_id' => (int) $this->ascendancy_type_id,
            'class_name'  => $this->whenLoaded('type', fn () => $this->type?->name),
            'portrait_url' => $this->portrait_url,
            'equipment'   => [],
            'spells' => $this->whenLoaded('type', function () use ($request) {
                $level = (int) $this->level;
                $asc   = $this->ascendancy_type_id; // peut être null

                return ($this->spells ?? collect())->map(function ($spell) use ($level, $asc, $request) {
                    // base = payload normal du sort via SpellResource
                    $payload = (new SpellResource($spell))->toArray($request);

                    $unlockLevel = (int) ($spell->pivot->unlock_level ?? 1);
                    $reqSpec     = $spell->pivot->required_specialization ?? null;

                    // on enrichit le payload côté Team
                    $payload['unlock_level']            = $unlockLevel;
                    $payload['required_specialization'] = $reqSpec;
                    $payload['unlocked']                = $unlockLevel <= $level
                        && ($reqSpec === null || (string) $reqSpec === (string) $asc);

                    return $payload;
                })->values();
            }),
            'stats'       => $this->mapStats($this->hp, $this->strength, $this->power, $this->defense),
        ];
    }

    protected function mapStats($hp, $str, $power, $def)
    {
        $arr = [];
        $arr['hp'] = $this->hp;
        $arr['strength'] = $this->strength;
        $arr['power'] = $this->power;
        $arr['defense'] = $this->defense;

        return $arr;
    }
}
