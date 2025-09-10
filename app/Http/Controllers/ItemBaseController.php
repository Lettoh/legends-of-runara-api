<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreItemBaseRequest;
use App\Http\Requests\UpdateItemBaseRequest;
use App\Http\Resources\ItemBaseResource;
use App\Models\ItemBase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ItemBaseController extends Controller
{
    public function index(Request $request)
    {
        $q = ItemBase::query()
            ->with('slot')
            ->when($request->string('search'), function ($query, $search) {
                $s = "%{$search}%";
                $query->where('name', 'like', $s);
            })
            ->when($request->filled('slot_id'), fn($query) => $query->where('slot_id', (int)$request->input('slot_id')))
            ->orderBy('name');

        $per = (int) $request->input('per_page', 50);
        $bases = $per > 0 ? $q->paginate($per) : $q->get();

        return ItemBaseResource::collection($bases);
    }

    public function show(int $id)
    {
        $base = ItemBase::with('slot')->findOrFail($id);
        return new ItemBaseResource($base);
    }

    public function store(StoreItemBaseRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('item_bases', 'public');
            $data['image'] = Storage::url($path); // => /storage/item_bases/xxx.webp
        }

        $base = ItemBase::create($data);
        return (new ItemBaseResource($base->load('slot')))->response()->setStatusCode(201);
    }

    public function update(UpdateItemBaseRequest $request, int $id)
    {
        $base = ItemBase::findOrFail($id);
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($base->image && str_starts_with($base->image, '/storage/')) {
                $old = str_replace('/storage/', '', $base->image);
                Storage::disk('public')->delete($old);
            }
            $path = $request->file('image')->store('item_bases', 'public');
            $data['image'] = Storage::url($path);
        }

        $base->update($data);
        return new ItemBaseResource($base->load('slot'));
    }

    public function destroy(int $id)
    {
        $base = ItemBase::findOrFail($id);
        // Option: bloquer la suppression si des items existent déjà sur cette base
        // if ($base->items()->exists()) { return response()->json(['message'=>'Base used by items'], 422); }
        $base->delete();
        return response()->noContent();
    }

    public function recipe(int $id)
    {
        $base = ItemBase::with(['slot','recipeIngredients'])->findOrFail($id);
        return new ItemBaseResource($base);
    }

    public function updateRecipe(Request $request, int $id)
    {
        $data = $request->validate([
            'ingredients'                 => ['required','array','min:1'],
            'ingredients.*.resource_id'   => ['required','integer','exists:resources,id'],
            'ingredients.*.quantity'      => ['required','integer','min:1','max:1000000'],
        ]);

        $base = ItemBase::findOrFail($id);

        $pairs = collect($data['ingredients'])
            ->groupBy('resource_id')
            ->map(fn($g)=>['quantity' => (int) max(1, $g->first()['quantity'])])
            ->toArray();

        $base->recipeIngredients()->sync([]);     // reset
        $base->recipeIngredients()->sync($pairs); // set

        return new ItemBaseResource($base->load(['slot','recipeIngredients']));
    }
}
