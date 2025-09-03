<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttachSpellToClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // plug your policies/middlewares if needed
    }

    public function rules(): array
    {
        return [
            'unlock_level' => ['required','integer','min:1','max:255'],
            'required_specialization' => ['nullable','string','max:60'],
        ];
    }
}
