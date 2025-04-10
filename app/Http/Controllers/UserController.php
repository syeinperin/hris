<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Main user listing.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Optional: filter by search, role, status, etc.
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $roleMap = [
                'admin'      => 1,
                'hr'         => 2,
                'employee'   => 3,
                'supervisor' => 4,
                'timekeeper' => 5,
            ];
            $selectedRole = $request->role;
            if (isset($roleMap[$selectedRole])) {
                $query->where('role_id', $roleMap[$selectedRole]);
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $query->orderBy($request->input('sort_by', 'created_at'), $request->input('sort_order', 'desc'));

        $users = $query->paginate(10);
        return view('users.index', compact('users'));
    }

    /**
     * Show only pending users (status = 'pending').
     */
    public function pending()
    {
        // Fetch users whose status is 'pending'.
        $users = User::where('status', 'pending')->paginate(10);
        return view('users.pending', compact('users'));
    }

    /**
     * Remove the specified user (and associated employee if any).
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // If there's a corresponding employee, delete it
        if ($user->employee) {
            $user->employee->delete();
        }

        $user->delete();

        return redirect()->route('users.index')
                         ->with('success', 'User and corresponding employee data removed!');
    }
}
