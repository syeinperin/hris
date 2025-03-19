<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (!$request->user()) {
            abort(403, 'Unauthorized');
        }

        // Ensure role is loaded and check role name
        if ($request->user()->role && $request->user()->role->name !== $role) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}


