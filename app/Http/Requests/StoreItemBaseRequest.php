<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemBaseRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'slot_id'             => ['required','integer','exists:equipment_slots,id'],
            'name'                => ['required','string','max:190','unique:item_bases,name'],
            'ilvl_req'            => ['required','integer','min:1','max:999'],
            'image'               => ['nullable','image','mimes:png,jpg,jpeg,webp','max:6144'],

            'implicit_stat_code'  => ['nullable','string','in:strength,power,defense,hp,speed,crit_chance,crit_damage'],
            'implicit_min'        => ['nullable','integer'],
            'implicit_max'        => ['nullable','integer'],

            'base_crit_chance'    => ['required','numeric','min:0','max:100'], // 0 pour non-armes
        ];
    }
}
