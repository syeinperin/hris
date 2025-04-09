<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Role;
use App\Models\User;
use App\Models\Schedule; // Import Schedule model
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    /**
     * Display the employee listing with the add employee form (unified view).
     */
    public function index(Request $request)
    {
        $query = Employee::with(['department', 'designation', 'user', 'schedule']);

        // Optional: Filter by department
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Optional: Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }

        $employees = $query->latest()->get();
        $departments = Department::all();
        $designations = Designation::all();
        $schedules = Schedule::orderBy('name')->get(); // Load available schedules

        return view('employees.index', compact('employees', 'departments', 'designations', 'schedules'));
    }

    /**
     * The create method now returns the same unified view.
     */
    public function create()
    {
        // We simply redirect to index (unified view) if separate create view is not desired.
        return redirect()->route('employees.index');
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'email'           => 'required|email|unique:users,email',
                'password'        => 'required|min:8',
                'role'            => 'required|in:admin,hr,employee,supervisor,timekeeper',
                'status'          => 'required|in:active,inactive',
                'first_name'      => 'required|string|max:255',
                'middle_name'     => 'nullable|string|max:255',
                'last_name'       => 'required|string|max:255',
                'gender'          => 'required',
                'dob'             => 'required|date',
                'current_address' => 'required|string|max:255',
                'permanent_address'=> 'nullable|string|max:255',
                'father_name'     => 'nullable|string|max:255',
                'mother_name'     => 'nullable|string|max:255',
                'previous_company'=> 'nullable|string|max:255',
                'job_title'       => 'nullable|string|max:255',
                'years_experience'=> 'nullable|numeric|min:0',
                'nationality'     => 'nullable|string|max:255',
                'department_id'   => 'required|exists:departments,id',
                'designation_id'  => 'required|exists:designations,id',
                'schedule_id'     => 'nullable|exists:schedules,id',  
                'fingerprint_id'  => 'nullable|string|unique:employees,fingerprint_id',
                'profile_picture' => 'nullable|image',
            ]);

            $role = Role::where('name', $data['role'])->firstOrFail();

            // Create user record first
            $user = new User();
            $user->name  = $data['first_name'] . ' ' . $data['last_name'];
            $user->email = $data['email'];
            $user->password = bcrypt($data['password']);
            $user->role_id  = $role->id;
            $user->status   = $data['status'];
            $user->save();

            // Create employee record and link with user
            $employee = new Employee();
            $employee->fill($data);
            $employee->name    = $data['first_name'] . ' ' . $data['last_name'];
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

    /**
     * Show the form for editing the specified employee.
     */
    public function edit($id)
    {
        $employee = Employee::with(['department', 'designation', 'user', 'schedule'])->findOrFail($id);
        $departments = Department::all();
        $designations = Designation::all();
        $schedules = Schedule::orderBy('name')->get();

        return view('employees.edit', compact('employee', 'departments', 'designations', 'schedules'));
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, $id)
    {
        $employee = Employee::with('user')->findOrFail($id);

        $data = $request->validate([
            'email'           => 'required|email|unique:users,email,' . $employee->user->id,
            'role'            => 'required|in:admin,hr,employee,supervisor,timekeeper',
            'status'          => 'required|in:active,inactive',
            'first_name'      => 'required|string|max:255',
            'middle_name'     => 'nullable|string|max:255',
            'last_name'       => 'required|string|max:255',
            'gender'          => 'required',
            'dob'             => 'required|date',
            'current_address' => 'required|string|max:255',
            'permanent_address'=> 'nullable|string|max:255',
            'father_name'     => 'nullable|string|max:255',
            'mother_name'     => 'nullable|string|max:255',
            'previous_company'=> 'nullable|string|max:255',
            'job_title'       => 'nullable|string|max:255',
            'years_experience'=> 'nullable|numeric|min:0',
            'nationality'     => 'nullable|string|max:255',
            'department_id'   => 'required|exists:departments,id',
            'designation_id'  => 'required|exists:designations,id',
            'schedule_id'     => 'nullable|exists:schedules,id',
            'fingerprint_id'  => 'nullable|string|unique:employees,fingerprint_id,' . $employee->id,
            'profile_picture' => 'nullable|image',
        ]);

        // Update the related user
        $employee->user->email = $data['email'];
        $employee->user->name  = $data['first_name'] . ' ' . $data['last_name'];
        $employee->user->role_id = Role::where('name', $data['role'])->firstOrFail()->id;
        $employee->user->status = $data['status'];
        if($request->filled('password')) {
            $employee->user->password = bcrypt($request->password);
        }
        $employee->user->save();

        // Update employee record
        $employee->fill($data);
        $employee->name = $data['first_name'] . ' ' . $data['last_name'];

        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/profile_pictures'), $filename);
            $employee->profile_picture = 'uploads/profile_pictures/' . $filename;
        }

        $employee->save();

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully!');
    }

    /**
     * Remove the specified employee from storage.
     */
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        // Optionally, you might want to delete the related user as well.
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully!');
    }
}
