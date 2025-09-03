<?php

namespace App\Http\Controllers;

use App\Models\CharacterType;

class CharacterTypeController extends Controller
{
    public function index()
    {
        $rows = CharacterType::query()
            ->orderBy('id')
            ->get(['id','name']);

        return response()->json([ 'data' => $rows ]);
    }
}
