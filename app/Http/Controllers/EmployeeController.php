<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with(['department', 'designation'])->latest()->get();
        $departments = Department::all();
        $designations = Designation::all();
        $roles = Role::all();

        return view('employees.index', compact('employees', 'departments', 'designations', 'roles'));
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8',
                'role' => 'required|in:admin,hr,employee,supervisor,timekeeper',
                'status' => 'required|in:active,inactive',
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
                'fingerprint_id' => 'nullable|string|unique:employees,fingerprint_id',
                'profile_picture' => 'nullable|image',
            ]);

            $role = Role::where('name', $data['role'])->firstOrFail();

            $user = new User();
            $user->name = $data['first_name'] . ' ' . $data['last_name']; 
            $user->email = $data['email'];
            $user->password = bcrypt($data['password']);
            $user->role_id = $role->id;
            $user->status = $data['status'];
            $user->save();


            $employee = new Employee();
            $employee->fill($data);
            $employee->name = $data['first_name'] . ' ' . $data['last_name'];
            $employee->user_id = $user->id;

            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                $filename = time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/profile_pictures'), $filename);
                $employee->profile_picture = 'uploads/profile_pictures/' . $filename;
            }

            $employee->save();

            return redirect()->route('employees.index')->with('success', 'Employee and user created successfully!');
        } catch (\Exception $e) {
            Log::error('Employee store error: ' . $e->getMessage());
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
}
