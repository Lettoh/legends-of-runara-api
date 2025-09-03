<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSpellEffectRequest extends FormRequest
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
            'kind'           => ['required', Rule::in($kinds)],
            'mode'           => ['required', Rule::in($modes)],
            'value'          => ['nullable','integer'],
            'vs'             => ['nullable', Rule::in($vs)],
            'duration_turns' => ['required','integer','min:0','max:255'],
            'chance'         => ['nullable','integer','min:0','max:100'],
        ];
    }
}
