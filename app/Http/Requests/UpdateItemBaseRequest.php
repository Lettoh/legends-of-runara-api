<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemBaseRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = (int) $this->route('id');

        return [
            'slot_id'             => ['sometimes','integer','exists:equipment_slots,id'],
            'name'                => ['sometimes','string','max:190', Rule::unique('item_bases','name')->ignore($id)],
            'ilvl_req'            => ['sometimes','integer','min:1','max:999'],
            'image'               => ['sometimes','nullable','image','mimes:png,jpg,jpeg,webp','max:6144'],

            'implicit_stat_code'  => ['nullable','string','in:strength,power,defense,hp,speed,crit_chance,crit_damage'],
            'implicit_min'        => ['nullable','integer'],
            'implicit_max'        => ['nullable','integer'],

            'base_crit_chance'    => ['sometimes','numeric','min:0','max:100'],
        ];
    }
}
