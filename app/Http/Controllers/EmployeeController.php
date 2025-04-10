<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Schedule;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    /**
     * Display the employee listing with a unified view.
     */
    public function index(Request $request)
    {
        $query = Employee::with(['department', 'designation', 'user', 'schedule']);

        // Optional filters (e.g. search by name, department, etc.)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->input('department_id'));
        }
        $employees = $query->latest()->get();
        $departments = Department::all();
        $designations = Designation::all();
        $schedules = Schedule::orderBy('name')->get();

        return view('employees.index', compact('employees', 'departments', 'designations', 'schedules'));
    }

    /**
     * Store a newly created employee.
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

            $user = new User();
            $user->name  = $data['first_name'] . ' ' . $data['last_name'];
            $user->email = $data['email'];
            $user->password = bcrypt($data['password']);
            $user->role_id  = $role->id;
            $user->status   = $data['status'];
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

    /**
     * Delete an employee and its related user.
     */
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        // The Employee model's booted event will delete the associated user.
        $employee->delete();

        return redirect()->route('employees.index')
                         ->with('success', 'Employee and corresponding user deleted successfully!');
    }

    // ... other methods such as edit(), update(), etc.
}
