<?php

namespace App\Http\Controllers;

use App\Http\Requests\EquipItemRequest;
use App\Models\{Character, CharacterEquipment, EquipmentSlot, Item};
use Illuminate\Support\Facades\DB;

class CharacterEquipmentController extends Controller
{
    public function equip(EquipItemRequest $request, Character $character)
    {
        $itemId = (string) $request->input('item_id');
        $item = Item::with('base.slot')->findOrFail($itemId);

        // L’item doit appartenir au même owner que le perso
        if ((int) $item->owner_user_id !== (int) $character->user_id) {
            return response()->json(['message' => 'Item does not belong to this character\'s owner.'], 403);
        }

        $slotId = (int) $item->base->slot_id;

        return DB::transaction(function () use ($character, $item, $slotId) {
            CharacterEquipment::where('character_id', $character->id)
                ->where('slot_id', $slotId)
                ->delete();

            CharacterEquipment::create([
                'character_id' => $character->id,
                'item_id'      => $item->id,
                'slot_id'      => $slotId,
                'equipped_at'  => now(),
            ]);

            return response()->json(['ok' => true]);
        });
    }

    // Déséquipe un slot
    public function unequip(Character $character, EquipmentSlot $slot): \Illuminate\Http\JsonResponse
    {
        CharacterEquipment::where('character_id', $character->id)
            ->where('slot_id', $slot->id)
            ->delete();

        return response()->json(['ok' => true]);
    }
}
