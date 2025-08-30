<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ZoneController extends Controller
{
    public function show(Zone $zone)
    {
        return response()->json([
            'zone' => $zone,
        ]);
    }

    public function index(Request $request)
    {
        $q = Zone::query();

        if ($request->boolean('select')) {
            return response()->json([
                'data' => $q->get(['id','name']),
            ]);
        }

        if ($search = trim((string) $request->query('search', ''))) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }


        $sort = in_array($request->query('sort'), ['name','created_at','min_level', 'max_level']) ? $request->query('sort') : 'name';
        $dir  = $request->query('dir') === 'desc' ? 'desc' : 'asc';
        $q->orderBy($sort, $dir);


        $perPage = (int) $request->query('per_page', 12);
        $perPage = $perPage > 100 ? 100 : ($perPage < 1 ? 12 : $perPage);


        return response()->json($q->paginate($perPage));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'min_level' => ['required', 'integer'],
            'max_level' => ['required', 'integer'],
            'image'       => ['nullable','image','mimes:png,jpg,jpeg,webp','max:6144'],
        ]);

        if ($request->hasFile('image')) {

            $ext = $request->file('image')->getClientOriginalExtension();
            $filename = 'runara_'.Str::slug($data['name']).'_'.Str::random(8).'.'.$ext;


            $request->file('image')->storeAs('public/zones', $filename);

            $data['image'] = $filename;
        }

        $zone = Zone::create($data);

        return response()->json([
            'zone' => $zone->fresh(),
        ], 201);
    }

    public function update(Request $request, Zone $zone)
    {
        $data = $request->validate([
            'name'        => ['sometimes','string','max:255'],
            'description' => ['nullable','string'],
            'image'       => ['nullable','image','mimes:png,jpg,jpeg,webp','max:2048'],
            'min_level' => ['nullable', 'integer'],
            'max_level' => ['nullable', 'integer'],
        ]);

        if ($request->hasFile('image')) {

            if ($zone->image && Storage::exists('public/zones/'.$zone->image)) {
                Storage::delete('public/zones/'.$zone->image);
            }

            $base = $data['name'] ?? $zone->name;
            $ext = $request->file('image')->getClientOriginalExtension();
            $filename = 'runara_'.Str::slug($base).'_'.Str::random(8).'.'.$ext;

            $request->file('image')->storeAs('public/zones', $filename);
            $data['image'] = $filename;
        }

        $zone->update($data);

        return response()->json(['zone' => $zone->fresh()]);
    }

    public function destroy(Zone $zone)
    {
        if ($zone->image && Storage::exists('public/zones/'.$zone->image)) {
            Storage::delete('public/zones/'.$zone->image);
        }

        $zone->delete();
        return response()->noContent();
    }
}
