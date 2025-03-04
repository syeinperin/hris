<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with('user')->get();
        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        return view('employees.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'position' => 'required|string|max:255',
            'department' => 'required|string|max:255',
        ]);

        $user = User::create($request->only('name', 'email', 'password'));
        $user->roles()->attach($request->role_id); // Assuming role_id is passed

        Employee::create(array_merge($request->all(), ['user_id' => $user->id]));
        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }

    // Other methods (edit, update, destroy) can be implemented similarly
}