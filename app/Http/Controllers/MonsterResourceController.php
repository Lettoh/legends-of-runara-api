<?php

namespace App\Http\Controllers;

use App\Models\Monster;
use App\Models\Resource;
use Illuminate\Http\Request;

class MonsterResourceController extends Controller
{
    public function index(Monster $monster): \Illuminate\Http\JsonResponse
    {
        $data = $monster->resources()
            ->select('resources.id','name')
            ->get()
            ->map(function($r){
                $r->icon_url = $r->icon_url; // force append for clarity
                return $r;
            });

        return response()->json(['data' => $data]);
    }

    public function store(Request $request, Monster $monster): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'resource_id' => ['required','exists:resources,id'],
            'drop_chance' => ['required','numeric','between:0,100'],
            'min_qty'     => ['nullable','integer','min:1'],
            'max_qty'     => ['nullable','integer','min:1'],
        ]);
        $min = $data['min_qty'] ?? 1;
        $max = $data['max_qty'] ?? 1;

        $monster->resources()->syncWithoutDetaching([
            $data['resource_id'] => [
                'drop_chance' => $data['drop_chance'],
                'min_qty' => $min,
                'max_qty' => max($min, $max),
            ],
        ]);

        $res = Resource::find($data['resource_id']);
        return response()->json(['resource' => $res->fresh()]);
    }

    public function update(Request $request, Monster $monster, Resource $resource): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'drop_chance' => ['sometimes','numeric','between:0,100'],
            'min_qty'     => ['sometimes','integer','min:1'],
            'max_qty'     => ['sometimes','integer','min:1'],
        ]);

        $pivot = array_filter([
            'drop_chance' => $data['drop_chance'] ?? null,
            'min_qty'     => $data['min_qty'] ?? null,
            'max_qty'     => isset($data['max_qty'])
                ? max($data['min_qty'] ?? 1, $data['max_qty'])
                : null,
        ], fn($v) => !is_null($v));

        $monster->resources()->updateExistingPivot($resource->id, $pivot);
        return response()->json(['ok' => true]);
    }

    public function destroy(Monster $monster, Resource $resource): \Illuminate\Http\Response
    {
        $monster->resources()->detach($resource->id);
        return response()->noContent();
    }
}
