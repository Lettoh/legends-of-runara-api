<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ResourceController extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $q = $request->string('q')->toString();
        $selectOnly = $request->boolean('select');

        $query = Resource::query()
            ->when($q, fn($qq) => $qq->where('name','like',"%$q%"));

        if ($selectOnly) {
            return response()->json(['data' => $query->orderBy('name')->get(['id','name'])]);
        }

        return response()->json(['data' => $query->orderBy('name')->get()]);
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'name'        => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'rarity'      => ['required','integer','between:1,5'],
            'tradeable'   => ['required','boolean'],
            'icon'        => ['nullable','image','mimes:png,jpg,jpeg,webp','max:6144'],
        ]);

        if ($request->hasFile('icon')) {
            $ext = $request->file('icon')->getClientOriginalExtension();
            $filename = 'res_'.Str::slug($data['name']).'_'.Str::random(8).'.'.$ext;
            $request->file('icon')->storeAs('public/resources', $filename);
            $data['icon'] = $filename;
        }

        $res = Resource::create($data);
        return response()->json(['resource' => $res->fresh()], 201);
    }

    public function update(Request $request, Resource $resource): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'name'        => ['sometimes','string','max:255'],
            'description' => ['nullable','string'],
            'rarity'      => ['sometimes','integer','between:1,5'],
            'tradeable'   => ['sometimes','boolean'],
            'icon'        => ['nullable','image','mimes:png,jpg,jpeg,webp','max:6144'],
        ]);

        if ($request->hasFile('icon')) {
            if ($resource->icon && Storage::exists('public/resources/'.$resource->icon)) {
                Storage::delete('public/resources/'.$resource->icon);
            }
            $base = $data['name'] ?? $resource->name;
            $ext = $request->file('icon')->getClientOriginalExtension();
            $filename = 'res_'.Str::slug($base).'_'.Str::random(8).'.'.$ext;
            $request->file('icon')->storeAs('public/resources', $filename);
            $data['icon'] = $filename;
        }

        $resource->update($data);
        return response()->json(['resource' => $resource->fresh()]);
    }

    public function destroy(Resource $resource): \Illuminate\Http\Response
    {
        if ($resource->icon && Storage::exists('public/resources/'.$resource->icon)) {
            Storage::delete('public/resources/'.$resource->icon);
        }
        $resource->delete();
        return response()->noContent();
    }
}
