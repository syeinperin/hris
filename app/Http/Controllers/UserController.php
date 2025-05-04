<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a paginated list of users, with search + role + status filters,
     * always sorted by ID ascending.
     */
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $role   = $request->input('role', '');
        $status = $request->input('status', '');

        $query = User::query();

        // Search on name/email
        if ($search) {
            $like = "%{$search}%";
            $query->where(fn($q) =>
                $q->where('name','like',$like)
                  ->orWhere('email','like',$like)
            );
        }

        // Role filter (text->id map)
        if ($role) {
            $map = [
                'admin'      => 1,
                'hr'         => 2,
                'employee'   => 3,
                'supervisor' => 4,
                'timekeeper' => 5,
            ];
            if (isset($map[$role])) {
                $query->where('role_id', $map[$role]);
            }
        }

        // Status filter: active = has logged in, inactive = never
        if ($status === 'active') {
            $query->whereNotNull('last_login');
        } elseif ($status === 'inactive') {
            $query->whereNull('last_login');
        }

        // force ORDER BY id ASC
        $users = $query
            ->orderBy('id', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('users.index', compact(
            'users','search','role','status'
        ));
    }

    /**
     * AJAX: update a single user’s role inline.
     */
    public function updateRole(Request $request, User $user)
    {
        $role = $request->validate([
            'role' => 'required|string|in:admin,hr,employee,supervisor,timekeeper'
        ])['role'];

        $map = [
            'admin'      => 1,
            'hr'         => 2,
            'employee'   => 3,
            'supervisor' => 4,
            'timekeeper' => 5,
        ];
        $user->role_id = $map[$role];
        $user->save();

        return response()->json([
            'message' => 'Role updated to '.ucfirst($role),
            'role'    => $role,
        ]);
    }

    /**
     * Show “change password” form.
     */
    public function editPassword(User $user)
    {
        return view('users.edit-password', compact('user'));
    }

    /**
     * Persist new password.
     */
    public function updatePassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|confirmed|min:8',
        ]);

        $user->password = bcrypt($request->password);
        $user->save();

        return redirect()->route('users.index')
                         ->with('success',"Password updated for {$user->name}");
    }

    /**
     * Delete a user.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success','User deleted.');
    }
}