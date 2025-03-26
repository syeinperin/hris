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
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:employees,email',
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'required|exists:designations,id',
            'fingerprint_id' => 'required|unique:employees,fingerprint_id',
            // other validations...
        ]);
    
        $employee = Employee::create([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'department_id' => $request->department_id,
            'designation_id' => $request->designation_id,
            'fingerprint_id' => $request->fingerprint_id,
            // etc...
        ]);
    
        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }    
}


