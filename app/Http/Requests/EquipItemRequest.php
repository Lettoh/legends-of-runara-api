<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EquipItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'item_id' => ['required','string'], // ULID string
        ];
    }
}
