<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;

class UserController extends Controller
{
    /**
     * GET /users
     */
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $role   = $request->input('role', '');
        $status = $request->input('status', '');

        $query = User::with('role');

        if ($search) {
            $term = "%{$search}%";
            $query->where(fn($q) =>
                $q->where('name','like',$term)
                  ->orWhere('email','like',$term)
            );
        }

        if ($role) {
            $query->whereHas('role', fn($q) =>
                $q->where('name', $role)
            );
        }

        if (in_array($status, ['active','inactive','pending'])) {
            $query->where('status', $status);
        }

        $users = $query
            ->orderBy('id','asc')
            ->paginate(10)
            ->withQueryString();

        // Pull all role names for both the filter dropdown & inline editor
        $allRoles = Role::orderBy('name')
                        ->pluck('name','name')
                        ->toArray();

        return view('users.index', [
            'users'    => $users,
            'search'   => $search,
            'role'     => $role,
            'status'   => $status,
            'allRoles' => $allRoles,
        ]);
    }

    /**
     * PUT|PATCH /users/{user}/role
     */
    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        $roleModel = Role::where('name', $request->role)->firstOrFail();
        $user->role()->associate($roleModel);
        $user->save();

        return response()->json([
            'message' => 'Role updated to ' . ucfirst($roleModel->name),
            'role'    => $roleModel->name,
        ], 200);
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