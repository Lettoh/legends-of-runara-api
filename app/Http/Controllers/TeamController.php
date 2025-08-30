<?php

namespace App\Http\Controllers;

use App\Http\Resources\TeamRessource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TeamController extends Controller
{
    public function index($user_id)
    {
        $characters = User::find($user_id)
            ->characters()
            ->with('type')
            ->get();

        return TeamRessource::collection($characters);
    }
}
