<?php

namespace App\Http\Controllers;

use App\Http\Resources\ItemResource;
use App\Models\{ItemBase, InventoryItem, Item, ItemMod, Affix};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CraftController extends Controller
{
    public function craft(Request $request)
    {
        // On ne prend QUE la base
        $data = $request->validate([
            'base_id' => ['required','integer','exists:item_bases,id'],
        ]);

        $user = $request->user();

        $base = ItemBase::with(['slot','recipeIngredients'])->findOrFail($data['base_id']);

        // iLvl = ilvl_req de la base
        $itemLevel = (int) max(1, (int)$base->ilvl_req);

        // Nombre d’affixes = random 1..4
        $modsToRoll = random_int(1, 4);

        $ingredients = $base->recipeIngredients->map(fn($res)=>[
            'resource_id' => $res->id,
            'qty'         => (int) $res->pivot->quantity
        ]);

        if ($ingredients->isEmpty()) {
            return response()->json(['message'=>'No recipe defined for this base.'], 422);
        }

        return DB::transaction(function () use ($user, $base, $ingredients, $itemLevel, $modsToRoll) {
            // Vérifier & consommer l’inventaire (lock pour éviter double spend)
            foreach ($ingredients as $ing) {
                $inv = InventoryItem::where('user_id',$user->id)
                    ->where('resource_id',$ing['resource_id'])
                    ->lockForUpdate()
                    ->first();
                if (!$inv || $inv->quantity < $ing['qty']) {
                    return response()->json(['message'=>'Not enough resources','missing_resource_id'=>$ing['resource_id']], 422);
                }
            }
            foreach ($ingredients as $ing) {
                InventoryItem::where('user_id',$user->id)
                    ->where('resource_id',$ing['resource_id'])
                    ->decrement('quantity', $ing['qty']);
            }

            // Créer l'item
            $item = new Item();
            $item->id            = (string) Str::ulid();
            $item->base_id       = $base->id;
            $item->owner_user_id = $user->id;
            $item->item_level    = $itemLevel;

            // Implicite
            if ($base->implicit_stat_code !== null && $base->implicit_min !== null && $base->implicit_max !== null) {
                $min = min($base->implicit_min, $base->implicit_max);
                $max = max($base->implicit_min, $base->implicit_max);
                $item->implicit_value = random_int($min, $max);
            }

            // Pool d’affixes autorisés par slot + tiers compatibles à l’iLvl
            $slotId = $base->slot_id;
            $affixes = Affix::query()
                ->whereHas('slots', fn($q)=>$q->where('slot_id', $slotId))
                ->with(['tiers' => function($q) use ($itemLevel){
                    $q->where('item_level_min', '<=', $itemLevel)
                        ->where('item_level_max', '>=', $itemLevel);
                }])
                ->get()
                ->filter(fn($a)=>$a->tiers->count() > 0)
                ->values();

            $pool = [];
            foreach ($affixes as $a) {
                foreach ($a->tiers as $tier) {
                    $pool[] = ['affix'=>$a, 'tier'=>$tier, 'weight'=>(int)$tier->weight];
                }
            }

            // Tirage
            $chosen = [];
            $usedCodes = [];
            while (count($chosen) < $modsToRoll && $pool) {
                $entry = $this->weightedPick($pool);
                $code  = $entry['affix']->code;
                if (in_array($code, $usedCodes, true)) {
                    $pool = array_values(array_filter($pool, fn($e)=>$e['affix']->code !== $code));
                    continue;
                }
                $chosen[]   = $entry;
                $usedCodes[] = $code;
                // empêche doublons du même affixe
                $pool = array_values(array_filter($pool, fn($e)=>$e['affix']->code !== $code));
            }

            $cnt = count($chosen);
            $item->rarity = $cnt >= 3 ? 'rare' : ($cnt >= 1 ? 'magic' : 'normal');
            $item->save();

            foreach ($chosen as $entry) {
                $t   = $entry['tier'];
                $min = min($t->min_value, $t->max_value);
                $max = max($t->min_value, $t->max_value);
                $val = random_int($min, $max);

                $mod = new ItemMod();
                $mod->item_id  = $item->id;
                $mod->affix_id = $entry['affix']->id;
                $mod->tier_id  = $t->id;
                $mod->value    = $val;
                $mod->is_locked = false;
                $mod->save();
            }

            return new ItemResource($item->load(['base.slot','mods.affix','mods.tier']));
        });
    }

    private function weightedPick(array $entries)
    {
        $total = array_sum(array_map(fn($e)=>$e['weight'], $entries));
        if ($total <= 0) return $entries[array_rand($entries)];
        $r = random_int(1, $total);
        $acc = 0;
        foreach ($entries as $e) {
            $acc += (int) $e['weight'];
            if ($r <= $acc) return $e;
        }
        return $entries[array_key_first($entries)];
    }
}
