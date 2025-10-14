<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /** Show the login form. */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /** Handle an authentication attempt (email OR contact number). */
    public function login(Request $request)
    {
        $request->validate([
            'login'    => ['required','string'],   // email or phone
            'password' => ['required','string'],
        ]);

        $identifier = trim($request->input('login'));
        $password   = $request->input('password');

        // Determine if identifier looks like an email
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);

        $user = null;

        if ($isEmail) {
            // Standard email login
            $user = User::where('email', $identifier)->first();
        } else {
            // Phone login — normalize and try to resolve user by Employee or User
            $msisdn = $this->normalizeMsisdn($identifier);

            // A) Phone stored on employees.contact_number (linked via user_id)
            $userId = Employee::where('contact_number', $msisdn)->value('user_id');
            if ($userId) {
                $user = User::find($userId);
            }

            // B) Optional fallback if you also keep phone on users.contact_number
            if (!$user) {
                $user = User::where('contact_number', $msisdn)->first();
            }
        }

        if (!$user || !Hash::check($password, $user->password)) {
            return back()
                ->withInput($request->only('login'))
                ->withErrors(['login' => 'These credentials do not match our records.']);
        }

        Auth::login($user, remember: (bool)$request->boolean('remember'));
        $request->session()->regenerate();

        // Stamp last_login if present
        if ($user->isFillable('last_login') || \Schema::hasColumn('users','last_login')) {
            $user->last_login = now();
            $user->save();
        }

        // Choose dashboard: employee vs admin
        $intended = ($user->role && strtolower($user->role->name) === 'employee')
            ? route('dashboard.employee')
            : route('dashboard');

        return redirect()->intended($intended);
    }

    /** Log the user out. */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    /**
     * Normalize PH mobile numbers to 11-digit 0XXXXXXXXXX.
     * Accepts: 09171234567, 9171234567, +639171234567, 639171234567, with spaces/dashes.
     */
    private function normalizeMsisdn(string $raw): string
    {
        $digits = preg_replace('/\D+/', '', $raw);

        if ($digits === '') {
            return $raw;
        }

        // Convert 63/639xx to 0xxxxxxxxxx
        if (str_starts_with($digits, '63')) {
            $digits = '0' . substr($digits, 2);
        } elseif (str_starts_with($digits, '9') && strlen($digits) === 10) {
            // 9171234567 -> 09171234567
            $digits = '0' . $digits;
        }

        // Hard-trim to 11 if needed
        if (strlen($digits) > 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 0, 11);
        }

        return $digits;
    }
}
