<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // Adjust according to your user model
use Carbon\Carbon;

class UserController extends Controller
{
    /**
     * Display a paginated list of users with filtering.
     */
    public function index(Request $request)
    {
        // Start with a query builder so you can apply filters
        $query = User::query();

        // Example filter by search term on name and email:
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role if provided (optional)
        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        // Filter by active status (optional)
        if ($request->filled('status')) {
            if ($request->status == 'active') {
                $query->where('is_active', 1);
            } elseif ($request->status == 'inactive') {
                $query->where('is_active', 0);
            }
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate results (10 per page)
        $users = $query->paginate(10);

        return view('users.index', compact('users'));
    }

    // Implement additional methods (create, store, edit, update, destroy, etc.)
    public function bulkAction(Request $request)
    {
        // Process bulk actions here...
    }

    public function updateRole(Request $request, $id)
    {
        // AJAX role update
    }

    public function changePassword(Request $request, $id)
    {
        // Update password logic
    }

    public function resetPassword(Request $request, $id)
    {
        // Reset password logic (send email with new password, etc.)
    }
}
