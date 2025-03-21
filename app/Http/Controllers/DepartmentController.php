<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller {
    public function index() {
        $departments = Department::latest()->paginate(10);
        return view('departments.index', compact('departments'));
    }

    public function create() {
        return view('departments.create');
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|unique:departments|max:255',
        ]);

        Department::create($request->all());
        return redirect()->route('departments.index')->with('success', 'Department added successfully.');
    }

    public function edit(Department $department) {
        return view('departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department) {
        $request->validate([
            'name' => 'required|unique:departments,name,' . $department->id,
        ]);

        $department->update($request->all());
        return redirect()->route('departments.index')->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department) {
        $department->delete();
        return redirect()->route('departments.index')->with('success', 'Department deleted successfully.');
    }
}
