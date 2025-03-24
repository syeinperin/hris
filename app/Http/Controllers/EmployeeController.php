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
        $employees = Employee::with('department', 'designation')->get();
        $departments = Department::all();
        $designations = Designation::all();

        return view('employees.index', compact('employees', 'departments', 'designations'));
    }

    // Show the employee creation form
    public function create()
    {
        $departments = Department::all();
        $designations = Designation::all();
        $employees = Employee::all();
        return view('employees.create', compact('employees', 'departments', 'designations'));
    }

    // Store a new employee
    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|unique:employees,email',
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
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

            // Ensure 'name' field is generated from first + last name
        $data['name'] = $request->input('first_name') . ' ' . $request->input('last_name');

        // Handle File Upload
        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $fileName = time() . '.' . $file->getClientOriginalExtension(); // Unique file name
            $file->move(public_path('uploads/profile_pictures'), $fileName); // Store in public/uploads/profile_pictures
            $data['profile_picture'] = 'uploads/profile_pictures/' . $fileName; // Save path in database
        }


        Employee::create($data);
        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }
}


