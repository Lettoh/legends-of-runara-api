<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEquipmentSlotRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('slot')?->id ?? $this->route('slot');

        return [
            'code' => ['sometimes','string','alpha_dash','max:50', Rule::unique('equipment_slots','code')->ignore($id)],
            'name' => ['sometimes','string','max:190'],
        ];
    }
}
