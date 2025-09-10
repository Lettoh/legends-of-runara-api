<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAffixRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = (int) $this->route('id');

        return [
            'code'         => ['sometimes','string','max:190', Rule::unique('affixes','code')->ignore($id)],
            'name'         => ['sometimes','string','max:190'],
            'stat_code'    => ['sometimes','string','in:strength,power,defense,hp,speed,crit_chance,crit_damage'],
            'kind'         => ['sometimes','in:prefix,suffix'],
            'effect'       => ['sometimes','in:add,percent'],
            'max_per_item' => ['sometimes','integer','min:1','max:3'],

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
