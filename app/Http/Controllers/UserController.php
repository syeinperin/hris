<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // ✅ Display a list of users
    // Display a list of users
    public function index()
    {
        $users = User::with('role')->get();
        $roles = Role::all(); // Add this line to fetch roles
        return view('users.index', compact('users', 'roles'));
    }


    // ✅ Show form to create new user
    public function create()
    {
        $departments = Department::all();
        $designations = Designation::all();
        $roles = Role::all(); // Get available roles
        return view('employees.create', compact('departments', 'designations', 'roles'));
    }

    // ✅ Store new user to database
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:admin,hr,employee,supervisor,timekeeper',
        ]);

        $role = Role::where('name', $request->role)->first();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        Auth::login($user);

        return redirect()->route('users.index')->with('success', 'User created and logged in successfully!');
    }

    // ✅ Show form to edit existing user
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        return view('users.edit', compact('user', 'roles'));
    }

    // ✅ Update existing user details
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|in:admin,hr,employee,supervisor,timekeeper',
            'status' => 'required|in:active,inactive',
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => Role::where('name', $request->role)->first()->id,
            'status' => $request->status,
        ]);

        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }

    // ✅ Delete a user
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully!');
    }

    // ✅ Show form to assign role to user
    public function assignRoleForm($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        return view('users.assign-role', compact('user', 'roles'));
    }

    // ✅ Save assigned role to user
    public function assignRoleStore(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->role_id = Role::where('name', $request->input('role'))->first()->id;
        $user->save();

        return redirect()->route('users.index')->with('success', 'Role assigned successfully!');
    }
}
