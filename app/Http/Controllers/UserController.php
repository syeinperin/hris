<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Employee;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $roleMap = [
                'admin'      => 1,
                'hr'         => 2,
                'employee'   => 3,
                'supervisor' => 4,
                'timekeeper' => 5,
            ];
            if (isset($roleMap[$request->role])) {
                $query->where('role_id', $roleMap[$request->role]);
            }
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $query->orderBy($request->input('sort_by', 'created_at'), $request->input('sort_order', 'desc'));

        $users = $query->paginate(10);

        return view('users.index', compact('users'));
    }

    /**
     * Display a list of pending users.
     */
    public function pending()
    {
        $users = User::where('status', 'pending')->paginate(10);
        return view('users.pending', compact('users'));
    }

    /**
     * Approve a pending user (change status to 'active').
     */
    public function approve($id)
    {
        $user = User::findOrFail($id);
        $user->status = 'active';
        $user->save();

        return redirect()->route('users.pending')
                         ->with('success', 'User approved successfully!');
    }

    /**
     * Delete a user and its associated employee.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->employee) {
            $user->employee->delete();
        }
        $user->delete();

        return redirect()->route('users.index')
                         ->with('success', 'User and corresponding employee data removed!');
    }

    // Optional: Other methods like bulkAction(), assignRole(), etc.
}
