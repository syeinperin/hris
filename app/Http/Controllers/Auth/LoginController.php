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

     protected function authenticated(Request $request, $user)
{
    $user->load('role'); // Ensure role relationship is loaded

    if (!$user->role) {  // Check if the role exists
        abort(403, "User has no assigned role.");
    }

    switch ($user->role->name ?? '') { // Use optional chaining to prevent errors
        case 'admin':
            return redirect('/admin/dashboard');
        case 'employee':
            return redirect('/employee/dashboard');
        default:
            return redirect('/home'); // Fallback redirect
    }
}
 

     
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }
}
