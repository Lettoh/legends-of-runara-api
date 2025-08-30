<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function retrieveUser($id)
    {
        $user = User::where('id', $id)->first();

        return response()->json($user);
    }
}
