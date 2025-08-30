<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserSearchController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'limit'  => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $term  = trim((string) $request->get('search', ''));
        $limit = (int) $request->get('limit', 10);

        $query = User::query();

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        // petite pagination (ou ->limit($limit)->get() si tu veux juste un array)
        $users = $query->orderBy('name')->paginate($limit);

        // ne renvoie que ce qui est utile
        $users->getCollection()->transform(function ($u) {
            return [
                'id'    => $u->id,
                'name'  => $u->name,
                'email' => $u->email,
            ];
        });

        return response()->json($users);
    }
}
