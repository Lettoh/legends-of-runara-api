<?php

namespace App\Http\Controllers;

use App\Http\Requests\{AttachSpellToClassRequest, UpdateSpellPivotRequest};
use App\Models\{CharacterType, Spell};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CharacterTypeSpellController extends Controller
{
    // List spells attached to a class (with pivot fields)
    public function index(Request $request, CharacterType $type)
    {
        $query = $type->spells()->orderBy('name');
        if ($request->boolean('with_effects')) {
            $query->with('effects');
        }
        $spells = $query->get();

        $data = $spells->map(function ($s) {
            return [
                'id'   => $s->id,
                'name' => $s->name,
                'image_url' => $s->image_url ?? null,
                'pivot'=> [
                    'unlock_level' => (int) ($s->pivot->unlock_level ?? 1),
                    'required_specialization' => $s->pivot->required_specialization,
                ],
                'effects' => $s->relationLoaded('effects')
                    ? $s->effects->map(fn($e) => [
                        'id' => $e->id,
                        'kind' => $e->kind,
                        'mode' => $e->mode,
                        'value' => $e->value,
                        'vs' => $e->vs,
                        'duration_turns' => (int) $e->duration_turns,
                        'chance' => (int) $e->chance,
                    ])
                    : null,
            ];
        });

        return response()->json([
            'type' => [ 'id' => $type->id, 'name' => $type->name ],
            'data' => $data,
        ]);
    }

    // Attach (or upsert) a spell to a class with pivot data
    public function store(AttachSpellToClassRequest $request, CharacterType $type, Spell $spell)
    {
        $data = $request->validated();

        DB::transaction(function () use ($type, $spell, $data) {
            $type->spells()->syncWithoutDetaching([
                $spell->id => [
                    'unlock_level' => $data['unlock_level'],
                    'required_specialization' => $data['required_specialization'] ?? null,
                ],
            ]);
        });

        return response()->json(['ok' => true], 201);
    }

    // Update existing pivot
    public function update(UpdateSpellPivotRequest $request, CharacterType $type, Spell $spell)
    {
        $data = $request->validated();

        // ensure exists
        $exists = $type->spells()->where('spells.id', $spell->id)->exists();
        if (!$exists) {
            return response()->json(['message' => 'Spell not attached to class.'], 404);
        }

        $type->spells()->updateExistingPivot($spell->id, $data);

        return response()->json(['ok' => true]);
    }

    // Detach
    public function destroy(CharacterType $type, Spell $spell)
    {
        $type->spells()->detach($spell->id);
        return response()->noContent();
    }
}
