<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSpellEffectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $kinds = ['defense','damage','elemental_weakness','stun','shield','dot'];
        $modes = ['percent','flat'];
        $vs    = ['strength','power'];

        return [
            'kind'           => ['sometimes', Rule::in($kinds)],
            'mode'           => ['sometimes', Rule::in($modes)],
            'value'          => ['nullable','integer'],
            'vs'             => ['nullable', Rule::in($vs)],
            'duration_turns' => ['sometimes','integer','min:0','max:255'],
            'chance'         => ['sometimes','integer','min:0','max:100'],
        ];
    }
}
