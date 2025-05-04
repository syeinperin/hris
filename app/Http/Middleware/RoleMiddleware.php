<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Check the authenticated user’s role name.
     */
    public function handle(Request $request, Closure $next, $role)
    {
        $user = $request->user();

        if (! $user || $user->role->name !== $role) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}