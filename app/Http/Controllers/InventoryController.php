<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Resource;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /** Liste de l’inventaire (ressources + quantités) */
    public function index(User $user)
    {
        $items = $user->inventoryItems()
            ->with('resource') // pour avoir name, rarity, icon_url…
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn($it) => [
                'resource_id' => $it->resource_id,
                'name'        => $it->resource->name,
                'rarity'      => $it->resource->rarity,
                'tradeable'   => (bool) $it->resource->tradeable,
                'icon_url'    => $it->resource->icon_url,
                'quantity'    => (int) $it->quantity,
                'is_locked'   => (bool) $it->is_locked,
                'updated_at'  => $it->updated_at,
            ])
            ->values();

        return response()->json(['data' => $items]);
    }

    /** Ajoute/empile une ressource (increment) */
    public function add(Request $request, User $user)
    {
        $data = $request->validate([
            'resource_id' => ['required', 'exists:resources,id'],
            'quantity'    => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($user, $data) {
            $row = InventoryItem::lockForUpdate()
                ->firstOrCreate([
                    'user_id'     => $user->id,
                    'resource_id' => $data['resource_id'],
                ], [
                    'quantity'  => 0,
                    'is_locked' => false,
                ]);

            $row->increment('quantity', $data['quantity']);
        });

        return response()->json(['ok' => true]);
    }

    /** Consomme/retire une quantité (decrement) */
    public function consume(Request $request, User $user)
    {
        $data = $request->validate([
            'resource_id' => ['required', 'exists:resources,id'],
            'quantity'    => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($user, $data) {
            /** @var InventoryItem|null $row */
            $row = InventoryItem::lockForUpdate()
                ->where('user_id', $user->id)
                ->where('resource_id', $data['resource_id'])
                ->first();

            if (!$row || $row->quantity < $data['quantity']) {
                abort(422, 'Quantité insuffisante.');
            }

            $row->decrement('quantity', $data['quantity']);
            if ($row->quantity <= 0) $row->delete();
        });

        return response()->json(['ok' => true]);
    }

    /** Fixe explicitement la quantité (set) */
    public function set(Request $request, User $user)
    {
        $data = $request->validate([
            'resource_id' => ['required', 'exists:resources,id'],
            'quantity'    => ['required', 'integer', 'min:0'],
            'is_locked'   => ['sometimes', 'boolean'],
        ]);

        DB::transaction(function () use ($user, $data) {
            $row = InventoryItem::lockForUpdate()
                ->firstOrNew([
                    'user_id'     => $user->id,
                    'resource_id' => $data['resource_id'],
                ]);

            $row->quantity  = $data['quantity'];
            if (array_key_exists('is_locked', $data)) {
                $row->is_locked = $data['is_locked'];
            }

            if ($row->quantity <= 0) {
                if ($row->exists) $row->delete();
            } else {
                $row->save();
            }
        });

        return response()->json(['ok' => true]);
    }

    /** Supprime totalement une ressource de l’inventaire */
    public function destroy(User $user, Resource $resource)
    {
        InventoryItem::where('user_id', $user->id)
            ->where('resource_id', $resource->id)
            ->delete();

        return response()->noContent();
    }
}
