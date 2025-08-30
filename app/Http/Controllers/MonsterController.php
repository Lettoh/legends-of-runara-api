<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Monster;

class MonsterController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();

        $monsters = Monster::query()
            ->with([
                'zones' => function ($q) {
                    $q->withPivot(['spawn_chance']);
                },
                'resources' => function ($q) {
                    $q->withPivot(['drop_chance', 'min_qty', 'max_qty']);
                },
            ])
            ->when($q, fn ($qq) => $qq->where(fn ($w) => $w
                ->where('name', 'like', "%{$q}%")
                ->orWhere('level', $q)
            ))
            ->orderBy('name')
            ->get()
            ->map(function ($m) {
                return [
                    'id'        => $m->id,
                    'name'      => $m->name,
                    'level'     => $m->level,
                    'hp'        => $m->hp,
                    'atk'       => $m->atk,
                    'def'       => $m->def,
                    'image_url' => $m->image_url,

                    'zones' => $m->zones->map(fn ($z) => [
                        'id'            => $z->id,
                        'name'          => $z->name,
                        'spawn_chance'  => (float) $z->pivot->spawn_chance,
                    ])->values(),

                    'drops' => $m->resources->map(function ($r) {
                        $iconUrl = $r->icon_url ?? ($r->icon ? asset('storage/resources/'.$r->icon) : null);
                        return [
                            'id'        => $r->id,
                            'name'      => $r->name,
                            'icon_url'  => $iconUrl,
                            'chance'    => (float) $r->pivot->drop_chance,
                            'min_qty'   => (float) $r->pivot->min_qty,
                            'max_qty'   => (float) $r->pivot->max_qty,
                        ];
                    })->values(),
                ];
            });

        return response()->json(['data' => $monsters]);
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => ['required','string','max:255'],
            'level' => ['required','integer','min:1'],
            'hp'    => ['required','integer','min:1'],
            'atk'   => ['required','integer','min:0'],
            'def'   => ['required','integer','min:0'],
            'image' => ['nullable','image','mimes:png,jpg,jpeg,webp','max:6144'],
        ]);

        if ($request->hasFile('image')) {
            $ext = $request->file('image')->getClientOriginalExtension();
            $filename = 'monster_'.Str::slug($data['name']).'_'.Str::random(8).'.'.$ext;
            $request->file('image')->storeAs('public/monsters', $filename);
            $data['image'] = $filename; // prévois la colonne 'image' si tu veux stocker le nom
        }

        $monster = Monster::create($data);

        return response()->json(['monster' => $monster->fresh()], 201);
    }

    public function update(Request $request, Monster $monster)
    {
        $data = $request->validate([
            'name'  => ['sometimes','string','max:255'],
            'level' => ['sometimes','integer','min:1'],
            'hp'    => ['sometimes','integer','min:1'],
            'atk'   => ['sometimes','integer','min:0'],
            'def'   => ['sometimes','integer','min:0'],
            'image' => ['nullable','image','mimes:png,jpg,jpeg,webp','max:6144'],
        ]);

        if ($request->hasFile('image')) {
            if ($monster->image && Storage::exists('public/monsters/'.$monster->image)) {
                Storage::delete('public/monsters/'.$monster->image);
            }
            $base = $data['name'] ?? $monster->name;
            $ext = $request->file('image')->getClientOriginalExtension();
            $filename = 'monster_'.Str::slug($base).'_'.Str::random(8).'.'.$ext;
            $request->file('image')->storeAs('public/monsters', $filename);
            $data['image'] = $filename;
        }

        $monster->update($data);

        return response()->json(['monster' => $monster->fresh()]);
    }

    public function destroy(Monster $monster)
    {
        if ($monster->image && Storage::exists('public/monsters/'.$monster->image)) {
            Storage::delete('public/monsters/'.$monster->image);
        }
        $monster->delete();
        return response()->noContent();
    }
}
