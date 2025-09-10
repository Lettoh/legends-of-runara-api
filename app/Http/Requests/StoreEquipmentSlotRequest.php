<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEquipmentSlotRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code' => ['required','string','alpha_dash','max:50','unique:equipment_slots,code'],
            'name' => ['required','string','max:190'],
        ];
    }
}
