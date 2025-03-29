<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // Display a list of users
    public function create()
    {
        $departments = Department::all();
        $designations = Designation::all();
        $roles = Role::all(); // Get available roles
        return view('employees.create', compact('departments', 'designations', 'roles'));
    }

    // Store a new user in the database
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:admin,hr,employee,supervisor,timekeeper',
        ]);

        // Find the role by its name
        $role = Role::where('name', $request->role)->first();

        // Create the user
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->role_id = $role->id; 
        $user->status = 'active';
        $user->save();

        // Automatically log the user in
        Auth::login($user);

        return redirect()->route('users.index')->with('success', 'User created and logged in successfully!');
    }

    // Show the form to edit an existing user
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('users.edit', compact('user'));
    }

    // Update user details
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|in:admin,hr,employee,supervisor,timekeeper',
            'status' => 'required|in:active,inactive',
        ]);

        // Find and update the user
        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role_id = Role::where('name', $request->role)->first()->id;
        $user->status = $request->status;
        $user->save();

        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }

    // Delete a user from the system
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully!');
    }

    // Show the form to assign a role to a user
    public function assignRoleForm($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();  
        return view('users.assign-role', compact('user', 'roles'));
    }

    // Store the assigned role to a user
    public function assignRoleStore(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->role_id = Role::where('name', $request->input('role'))->first()->id;
        $user->save();

        return redirect()->route('users.index')->with('success', 'Role assigned successfully!');
    }
}
