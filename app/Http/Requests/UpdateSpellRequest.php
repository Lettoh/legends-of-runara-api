<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSpellRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('effects')) {
            $effects = $this->input('effects');
            if (is_string($effects)) {
                $decoded = json_decode($effects, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $this->merge(['effects' => $decoded]);
                }
            }
            if ($effects === '' || $effects === null) {
                $this->merge(['effects' => []]);
            }
        }
    }

    public function rules(): array
    {
        $targets = ['enemy_single','enemy_all','ally_single','ally_all','self'];

        return [
            'name'         => ['sometimes','string','max:100'],

            'image'        => ['nullable','string','max:255', Rule::unique('spells','image')->ignore($this->route('spell'))],
            'image_file'   => ['nullable','image','mimes:png,jpg,jpeg,webp','max:4096'],

            'description'  => ['nullable','string'],
            'target'       => ['sometimes', Rule::in($targets)],
            'base_power'   => ['sometimes','integer','min:0','max:65535'],
            'scaling_str'  => ['sometimes','numeric','min:0','max:999.999'],
            'scaling_pow'  => ['sometimes','numeric','min:0','max:999.999'],
            'cooldown_turns' => ['sometimes','integer','min:0','max:255'],
            'meta'           => ['nullable','array'],

            'effects' => ['sometimes','array'],
        ];
    }
}
