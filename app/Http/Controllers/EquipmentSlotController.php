<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEquipmentSlotRequest;
use App\Http\Requests\UpdateEquipmentSlotRequest;
use App\Http\Resources\EquipmentSlotResource;
use App\Models\EquipmentSlot;

class EquipmentSlotController extends Controller
{
    public function index()
    {
        $slots = EquipmentSlot::query()->orderBy('id')->get();
        return EquipmentSlotResource::collection($slots);
    }

    public function store(StoreEquipmentSlotRequest $request)
    {
        $slot = EquipmentSlot::create([
            'code' => strtolower($request->input('code')),
            'name' => $request->input('name'),
        ]);

        return (new EquipmentSlotResource($slot))->response()->setStatusCode(201);
    }

    public function update(UpdateEquipmentSlotRequest $request, EquipmentSlot $slot)
    {
        $payload = $request->validated();
        if (isset($payload['code'])) $payload['code'] = strtolower($payload['code']);

        $slot->update($payload);
        return new EquipmentSlotResource($slot);
    }

    public function destroy(EquipmentSlot $slot)
    {
        // Optionnel: empêcher la suppression si référencé par item_bases
        // if ($slot->itemBases()->exists()) return response()->json(['message'=>'Slot used by item bases'], 422);

        $slot->delete();
        return response()->noContent();
    }
}
