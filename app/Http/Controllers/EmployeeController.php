<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Designation;

class EmployeeController extends Controller
{
    // Show the employee list
    public function index()
    {
        $employees = Employee::with('department', 'designation')->paginate(10);
        return view('employees.index', compact('employees'));
    }

    // Show the employee creation form
    public function create()
    {
        $departments = Department::all();
        $designations = Designation::all();
        return view('employees.create', compact('departments', 'designations'));
    }

    // Store a new employee
    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required',
            'dob' => 'required|date',
            'current_address' => 'required|string|max:255',
            'permanent_address' => 'nullable|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'previous_company' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'years_experience' => 'nullable|numeric|min:0',
            'nationality' => 'nullable|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'required|exists:designations,id',
        ]);

        Employee::create($data);
        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }
}


