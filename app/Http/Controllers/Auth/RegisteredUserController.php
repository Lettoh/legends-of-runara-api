<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): Response
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
//            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'password' => ['required', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->characters()->create([
            'name' => 'Roger',
            'level' => 1,
            'type_id' => 1,
            'ascendancy_type_id' => 0,
        ]);

        $user->characters()->create([
            'name' => 'Marcel',
            'level' => 1,
            'type_id' => 2,
            'ascendancy_type_id' => 0,
        ]);

        $user->characters()->create([
            'name' => 'Paul',
            'level' => 1,
            'type_id' => 3,
            'ascendancy_type_id' => 0,
        ]);

        $user->inventory()->create([
            'user_id' => $user->id,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return response()->noContent();
    }
}
