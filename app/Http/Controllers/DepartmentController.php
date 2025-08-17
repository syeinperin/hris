<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display departments (GET), optionally filtered by ?search=
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $term   = $search ? "%{$search}%" : null;

        $departments = Department::latest()
            ->when($term, fn($q) => $q->where('name','like',$term))
            ->paginate(10)
            ->withQueryString();

        return view('departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new department.
     */
    public function create()
    {
        return view('departments.create');
    }

    /**
     * Store a newly created department.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:departments|max:255',
        ]);

        Department::create($request->only('name'));

        return redirect()
            ->route('departments.index')
            ->with('success', 'Department added successfully.');
    }

    /**
     * Show the form for editing the specified department.
     * Passes all existing department names for the dropdown.
     */
    public function edit(Department $department)
    {
        $departmentNames = Department::orderBy('name')
            ->pluck('name')
            ->toArray();

        return view('departments.edit', compact('department', 'departmentNames'));
    }

    /**
     * Update the specified department.
     */
    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|unique:departments,name,'.$department->id.'|max:255',
        ]);

        $department->update($request->only('name'));

        return redirect()
            ->route('departments.index')
            ->with('success', 'Department updated successfully.');
    }

    /**
     * Remove the specified department.
     */
    public function destroy(Department $department)
    {
        $department->delete();

        return redirect()
            ->route('departments.index')
            ->with('success', 'Department deleted successfully.');
    }
}
