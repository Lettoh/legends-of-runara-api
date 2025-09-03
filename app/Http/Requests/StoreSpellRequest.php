<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSpellRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // plug into policies if needed
    }

    protected function prepareForValidation(): void
    {
        $effects = $this->input('effects');

        // si multipart: "[]" (string) -> array
        if (is_string($effects)) {
            $decoded = json_decode($effects, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->merge(['effects' => $decoded]);
            }
        }

        // si vide: force tableau vide
        if ($effects === '' || $effects === null) {
            $this->merge(['effects' => []]);
        }
    }

    public function rules(): array
    {
        $targets = ['enemy_single','enemy_all','ally_single','ally_all','self'];
        $kinds   = ['defense','damage','elemental_weakness','stun','shield','dot'];
        $modes   = ['percent','flat'];
        $vs      = ['strength','power'];

        return [
            'name'         => ['required','string','max:100'],

            // either provide 'image' (string path/url) or 'image_file' (uploaded file)
            'image'        => ['nullable','string','max:255', 'unique:spells,image'],
            'image_file'   => ['nullable','image','mimes:png,jpg,jpeg,webp','max:4096'],

            'description'  => ['nullable','string'],
            'target'       => ['required', Rule::in($targets)],
            'base_power'   => ['required','integer','min:0','max:65535'],
            'scaling_str'  => ['required','numeric','min:0','max:999.999'],
            'scaling_pow'  => ['required','numeric','min:0','max:999.999'],
            'cooldown_turns' => ['sometimes','integer','min:0','max:255'],
            'meta'           => ['nullable','array'],

            // optional nested effects
            'effects'                 => ['sometimes','array'],
            'effects.*.kind'          => ['required_with:effects', Rule::in($kinds)],
            'effects.*.mode'          => ['required_with:effects', Rule::in($modes)],
            'effects.*.value'         => ['nullable','integer'],
            'effects.*.vs'            => ['nullable', Rule::in($vs)],
            'effects.*.duration_turns'=> ['required_with:effects','integer','min:0','max:255'],
            'effects.*.chance'        => ['nullable','integer','min:0','max:100'],
        ];
    }
}
