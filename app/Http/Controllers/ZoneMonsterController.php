<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use App\Models\Monster;
use Illuminate\Http\Request;

class ZoneMonsterController extends Controller
{
    public function index(Request $request, Zone $zone)
    {
        $monsters = $zone->monsters()
            ->with('zones')
            ->with('resources')
            ->orderBy('level')
            ->get()
            ->map(function ($m) {
                return [
                    'id'            => $m->id,
                    'name'          => $m->name,
                    'level'         => $m->level,
                    'hp'            => $m->hp,
                    'atk'           => $m->atk,
                    'def'           => $m->def,
                    'image_url'     => $m->image_url,
                    'spawn_chance'  => (float) $m->pivot->spawn_chance,   // depuis monster_zone
                    'drops'         => $m->resources->map(fn($r) => [
                        'id'       => $r->id,
                        'name'     => $r->name,
                        'icon_url' => $r->icon_url,
                        'chance'   => (float) $r->pivot->drop_chance,          // depuis monster_resource
                        'min_qty' => (float) $r->pivot->min_qty,
                        'max_qty' => (float) $r->pivot->max_qty,
                    ])->values(),
                ];
            });

        return response()->json(['data' => $monsters]);
    }

    public function store(Request $request, Zone $zone)
    {
        $data = $request->validate([
            'monster_id' => ['required','exists:monster,id'],
            'spawn_chance'  => ['nullable','numeric','min:0','max:100'],
        ]);

        $attrs = ['spawn_chance' => $data['spawn_chance'] ?? 100];
        $zone->monsters()->syncWithoutDetaching([$data['monster_id']]);

        $m = $zone->monsters()->where('monster_id', $data['monster_id'])->first();

        return response()->json([
            'monster' => $m->fresh()->setAttribute('spawn_chance', (float)$m->pivot->spawn_chance),
        ], 201);
    }

    public function update(Request $request, Zone $zone, Monster $monster)
    {
        $data = $request->validate([
            'spawn_chance' => ['required','numeric','min:0','max:100'],
        ]);

        abort_unless(
            $zone->monsters()->where('monster_id', $monster->id)->exists(),
            404, 'Monster not attached to this zone.'
        );

        $zone->monsters()->updateExistingPivot($monster->id, [
            'spawn_chance' => $data['spawn_chance'],
        ]);

        return response()->json([
            'data' => [
                'zone_id'    => $zone->id,
                'monster_id' => $monster->id,
                'spawn_chance'     => (float) $data['spawn_chance'],
            ],
        ]);
    }

    public function destroy(Zone $zone, Monster $monster)
    {
        $zone->monsters()->detach($monster->id);
        return response()->noContent();
    }
}
