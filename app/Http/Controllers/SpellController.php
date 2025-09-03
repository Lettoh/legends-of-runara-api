<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use App\Http\Requests\{StoreSpellRequest, UpdateSpellRequest};
use App\Http\Resources\{SpellResource};
use App\Models\{Spell};
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{DB, Storage};

class SpellController extends Controller
{
    public function index(Request $request)
    {
        $query = Spell::query();

        if ($search = $request->string('q')->toString()) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('with_effects')) {
            $query->with('effects');
        }
        if ($request->boolean('with_character_types')) {
            $query->with('characterTypes');
        }

        $perPage = min(100, max(1, (int) $request->get('per_page', 15)));
        return SpellResource::collection($query->orderBy('name')->paginate($perPage));
    }

    public function show(Spell $spell, Request $request)
    {
        $spell->loadMissing(['effects','characterTypes']);
        return new SpellResource($spell);
    }

    public function store(StoreSpellRequest $request)
    {
        $payload = $request->validated();
        $effects = Arr::pull($payload, 'effects', []);

        // handle image upload if provided
        if ($request->hasFile('image_file')) {
            $ext = $request->file('image_file')->getClientOriginalExtension();
            $payload['image'] = 'runara_'.Str::slug($payload['name']).'_'.Str::random(8).'.'.$ext;

            $request->file('image_file')->storeAs('public/spells', $payload['image']);
        }

        return DB::transaction(function () use ($payload, $effects) {
            $spell = Spell::create($payload);

            if (!empty($effects)) {
                foreach ($effects as $ef) {
                    $spell->effects()->create([
                        'kind'           => $ef['kind'],
                        'mode'           => $ef['mode'],
                        'value'          => $ef['value'] ?? null,
                        'vs'             => $ef['vs'] ?? null,
                        'duration_turns' => $ef['duration_turns'],
                        'chance'         => $ef['chance'] ?? 100,
                    ]);
                }
            }

            $spell->load(['effects']);
            return (new SpellResource($spell))->response()->setStatusCode(201);
        });
    }

    public function update(UpdateSpellRequest $request, Spell $spell)
    {
        $payload = $request->validated();
        $replaceEffects = array_key_exists('effects', $payload);
        $effects = $payload['effects'] ?? null;
        unset($payload['effects']);

        if ($request->hasFile('image_file')) {
            // delete previous image if stored locally (optional)
            if ($spell->image && str_starts_with($spell->image, 'spells/')) {
                try { Storage::disk('public')->delete($spell->image); } catch (\Throwable $e) {}
            }
            $payload['image'] = $request->file('image_file')->store('spells', 'public');
        }

        return DB::transaction(function () use ($spell, $payload, $replaceEffects, $effects) {
            if (!empty($payload)) {
                $spell->update($payload);
            }

            if ($replaceEffects) {
                $spell->effects()->delete();
                if (is_array($effects)) {
                    foreach ($effects as $ef) {
                        $spell->effects()->create([
                            'kind'           => $ef['kind'],
                            'mode'           => $ef['mode'],
                            'value'          => $ef['value'] ?? null,
                            'vs'             => $ef['vs'] ?? null,
                            'duration_turns' => $ef['duration_turns'],
                            'chance'         => $ef['chance'] ?? 100,
                        ]);
                    }
                }
            }

            $spell->load(['effects','characterTypes']);
            return new SpellResource($spell);
        });
    }

    public function destroy(Spell $spell)
    {
        if ($spell->image && str_starts_with($spell->image, 'spells/')) {
            try { Storage::disk('public')->delete($spell->image); } catch (\Throwable $e) {}
        }
        $spell->delete();
        return response()->noContent();
    }
}
