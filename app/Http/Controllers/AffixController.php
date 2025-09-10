<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAffixRequest;
use App\Http\Requests\UpdateAffixRequest;
use App\Http\Resources\AffixResource;
use App\Models\{Affix, AffixTier};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AffixController extends Controller
{
    public function index(Request $request)
    {
        $q = Affix::query()
            ->with(['tiers' => fn($q) => $q->orderBy('tier'), 'slots']);

        // search
        if ($request->filled('search')) {
            $s = '%'.$request->input('search').'%';
            $q->where(function ($qq) use ($s) {
                $qq->where('code', 'like', $s)
                    ->orWhere('name', 'like', $s)
                    ->orWhere('stat_code', 'like', $s);
            });
        }

        // stat_code
        if ($request->filled('stat_code')) {
            $q->where('stat_code', $request->input('stat_code'));
        }

        $q->orderBy('code');

        $per  = (int) $request->input('per_page', 50);
        $rows = $per > 0 ? $q->paginate($per) : $q->get();

        return AffixResource::collection($rows);
    }


    public function show(int $id)
    {
        $affix = Affix::with(['tiers' => fn($q) => $q->orderBy('tier'), 'slots'])->findOrFail($id);
        return new AffixResource($affix);
    }

    public function store(StoreAffixRequest $request)
    {
        $payload = $request->validated();

        return DB::transaction(function () use ($payload) {
            $affix = Affix::create([
                'code'          => $payload['code'],
                'name'          => $payload['name'],
                'stat_code'     => $payload['stat_code'],
                'kind'          => $payload['kind'],
                'effect'        => $payload['effect'],
                'max_per_item'  => $payload['max_per_item'] ?? 1,
            ]);

            // rules de slots
            if (!empty($payload['slot_ids'])) {
                $affix->slots()->sync(array_values($payload['slot_ids']));
            }

            // tiers
            if (!empty($payload['tiers'])) {
                foreach ($payload['tiers'] as $t) {
                    AffixTier::create([
                        'affix_id'       => $affix->id,
                        'tier'           => (int)$t['tier'],
                        'min_value'      => (int)$t['min_value'],
                        'max_value'      => (int)$t['max_value'],
                        'item_level_min' => (int)$t['item_level_min'],
                        'item_level_max' => (int)$t['item_level_max'],
                        'weight'         => (int)$t['weight'],
                    ]);
                }
            }

            return (new AffixResource($affix->load(['tiers'=>fn($q)=>$q->orderBy('tier')],'slots')))
                ->response()->setStatusCode(201);
        });
    }

    public function update(UpdateAffixRequest $request, int $id)
    {
        $payload = $request->validated();

        return DB::transaction(function () use ($payload, $id) {
            $affix = Affix::findOrFail($id);

            $affix->update([
                'code'          => $payload['code']         ?? $affix->code,
                'name'          => $payload['name']         ?? $affix->name,
                'stat_code'     => $payload['stat_code']    ?? $affix->stat_code,
                'kind'          => $payload['kind']         ?? $affix->kind,
                'effect'        => $payload['effect']       ?? $affix->effect,
                'max_per_item'  => $payload['max_per_item'] ?? $affix->max_per_item,
            ]);

            if (array_key_exists('slot_ids', $payload)) {
                $affix->slots()->sync($payload['slot_ids'] ?? []);
            }

            if (array_key_exists('tiers', $payload)) {
                // simple stratégie: on remplace intégralement les tiers fournis
                $affix->tiers()->delete();
                foreach ($payload['tiers'] as $t) {
                    AffixTier::create([
                        'affix_id'       => $affix->id,
                        'tier'           => (int)$t['tier'],
                        'min_value'      => (int)$t['min_value'],
                        'max_value'      => (int)$t['max_value'],
                        'item_level_min' => (int)$t['item_level_min'],
                        'item_level_max' => (int)$t['item_level_max'],
                        'weight'         => (int)$t['weight'],
                    ]);
                }
            }

            return new AffixResource($affix->load(['tiers'=>fn($q)=>$q->orderBy('tier')],'slots'));
        });
    }

    public function destroy(int $id)
    {
        $affix = Affix::findOrFail($id);
        // Option: empêcher suppression si déjà utilisé par des items
        // if ($affix->tiers()->exists()) { ... }
        $affix->slots()->detach();
        $affix->tiers()->delete();
        $affix->delete();
        return response()->noContent();
    }
}
