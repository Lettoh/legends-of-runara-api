<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSpellPivotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'unlock_level' => ['sometimes','integer','min:1','max:255'],
            'required_specialization' => ['nullable','string','max:60'],
        ];
    }
}
