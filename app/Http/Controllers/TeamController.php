<?php

namespace App\Http\Controllers;

use App\Http\Resources\CharacterResource;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class TeamController extends Controller
{
    public function index($user_id)
    {
        try {
            $user = User::findOrFail($user_id);
            $characters = $user->characters()->with(['type.spells.effects'])->get();
            return CharacterResource::collection($characters);
        } catch (\Throwable $e) {
            Log::error($e);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
