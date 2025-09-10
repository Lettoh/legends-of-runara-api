<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InventoryMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user_id_parameter = $request->route('user');
        $user = $request->user();

        if (!$user->is_admin && $user->id !== $user_id_parameter)
        {
            return response()->json(['message' => 'Access Forbidden'], 403);
        }

        return $next($request);
    }
}
