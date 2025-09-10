<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAffixRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code'         => ['required','string','max:190','unique:affixes,code'],
            'name'         => ['required','string','max:190'],
            'stat_code'    => ['required','string','in:strength,power,defense,hp,speed,crit_chance,crit_damage'],
            'kind'         => ['required','in:prefix,suffix'],
            'effect'       => ['required','in:add,percent'],
            'max_per_item' => ['nullable','integer','min:1','max:3'],

            'slot_ids'     => ['nullable','array'],
            'slot_ids.*'   => ['integer','exists:equipment_slots,id'],

            'tiers'                  => ['nullable','array'],
            'tiers.*.tier'           => ['required','integer','min:1'],
            'tiers.*.min_value'      => ['required','integer'],
            'tiers.*.max_value'      => ['required','integer','gte:tiers.*.min_value'],
            'tiers.*.item_level_min' => ['required','integer','min:1'],
            'tiers.*.item_level_max' => ['required','integer','gte:tiers.*.item_level_min'],
            'tiers.*.weight'         => ['required','integer','min:1'],
        ];
    }
}
