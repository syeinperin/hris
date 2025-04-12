<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // Display the login form
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Process login attempt
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        // Attempt login and ensure that only users with status "active" are allowed
        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password'], 'status' => 'active'])) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        // If login fails, redirect back with an error message
        return back()->withErrors([
            'email' => 'Invalid credentials or your account is not active.',
        ])->withInput();
    }

    // Log the user out
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
