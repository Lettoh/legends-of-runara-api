<?php

namespace App\Http\Controllers;

use App\Http\Requests\{StoreSpellEffectRequest, UpdateSpellEffectRequest};
use App\Http\Resources\{SpellStatusEffectResource};
use App\Models\{Spell, SpellStatusEffect};

class SpellStatusEffectController extends Controller
{
    public function store(StoreSpellEffectRequest $request, Spell $spell)
    {
        $effect = $spell->effects()->create($request->validated());
        return (new SpellStatusEffectResource($effect))->response()->setStatusCode(201);
    }

    public function update(UpdateSpellEffectRequest $request, Spell $spell, SpellStatusEffect $effect)
    {
        if ($effect->spell_id !== $spell->id) {
            abort(404);
        }
        $effect->update($request->validated());
        return new SpellStatusEffectResource($effect);
    }

    public function destroy(Spell $spell, SpellStatusEffect $effect)
    {
        if ($effect->spell_id !== $spell->id) {
            abort(404);
        }
        $effect->delete();
        return response()->noContent();
    }
}
