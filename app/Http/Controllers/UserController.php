<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // 1) Search by name or email
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // 2) Filter by role (role_id)
        if ($request->filled('role')) {
            $roleMap = [
                'admin'      => 1,
                'hr'         => 2,
                'employee'   => 3,
                'supervisor' => 4,
                'timekeeper' => 5,
            ];
            $selectedRole = $request->input('role'); // e.g. "admin"
            if (isset($roleMap[$selectedRole])) {
                $query->where('role_id', $roleMap[$selectedRole]);
            }
        }

        // 3) Filter by status instead of is_active
        if ($request->filled('status')) {
            if ($request->status == 'active') {
                $query->where('status', 'active');
            } elseif ($request->status == 'inactive') {
                $query->where('status', 'inactive');
            }
        }

        // 4) Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // 5) Paginate
        $users = $query->paginate(10);

        return view('users.index', compact('users'));
    }

    // ... other methods ...
}
