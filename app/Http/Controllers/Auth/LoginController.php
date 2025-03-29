<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Handle what happens after user authentication.
     */
    protected function authenticated(Request $request, $user)
    {
        // Optional: Ensure role relationship is loaded
        $user->loadMissing('role');

        if (!$user->role) {
            abort(403, "This user has no role assigned.");
        }

        // ✅ Redirect all users to shared dashboard
        return redirect()->route('dashboard');
    }

    /**
     * Constructor with middleware setup.
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }
}
