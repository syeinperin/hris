<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     * If the user is authenticated but not 'active', log them out and redirect to login.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->status !== 'active') {
            Auth::logout();
            return redirect()
                ->route('login')
                ->withErrors(['Your account has been deactivated.']);
        }

        return $next($request);
    }
}
