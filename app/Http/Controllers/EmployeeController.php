<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\Employee;
use App\Models\User;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Schedule;
use App\Models\Role;
use App\Mail\NewEmployeeAccountMail;

class EmployeeController extends Controller
{
    /**
     * Display a listing of active employees with filter & search.
     */
    public function index(Request $request)
    {
        $query = Employee::with(['user', 'department', 'designation', 'schedule'])
                         // only show employees whose user is approved
                         ->whereHas('user', fn($q) => $q->where('status', 'active'));

        // Search by name / code / email
        if ($term = $request->input('search')) {
            $query->where(fn($q) => $q->where('name', 'like', "%{$term}%")
                                        ->orWhere('employee_code', 'like', "%{$term}%")
                                        ->orWhereHas('user', fn($u) =>
                                            $u->where('email', 'like', "%{$term}%")
                                        ));
        }

        // Filter by department
        if ($dept = $request->input('department_id')) {
            $query->where('department_id', $dept);
        }

        // Filter by designation
        if ($desig = $request->input('designation_id')) {
            $query->where('designation_id', $desig);
        }

        // You could also filter by employment_type if desired:
        // if ($type = $request->input('employment_type')) {
        //     $query->where('employment_type', $type);
        // }

        // paginate & carry query string filters, ordering IDs ascending
        $employees = $query
            ->orderBy('id', 'asc')
            ->paginate(10)
            ->withQueryString();

        $departments   = Department::orderBy('name')->pluck('name', 'id')->toArray();
        $designations  = Designation::orderBy('name')->pluck('name', 'id')->toArray();
        $schedules     = Schedule::orderBy('name')->pluck('name', 'id')->toArray();
        $roles         = Role::pluck('name', 'name')->toArray();

        return view('employees.index', compact(
            'employees', 'departments', 'designations', 'schedules', 'roles'
        ));
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create()
    {
        $departments   = Department::orderBy('name')->get();
        $designations  = Designation::orderBy('name')->get();
        $schedules     = Schedule::orderBy('name')->get();
        $roles         = Role::pluck('name', 'name')->toArray();

        // If you want to pass a list of employment types to the view:
        $employmentTypes = [
            'regular'      => 'Regular',
            'casual'       => 'Casual',
            'project'      => 'Project',
            'seasonal'     => 'Seasonal',
            'fixed-term'   => 'Fixed-term',
            'probationary' => 'Probationary',
        ];

        return view('employees.create', compact(
            'departments', 'designations', 'schedules', 'roles', 'employmentTypes'
        ));
    }

    /**
     * Store a newly created employee (and its user).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'email'            => 'required|email|unique:users,email',
            'password'         => 'required|min:8|confirmed',
            'role'             => 'required|in:admin,hr,employee,supervisor,timekeeper',
            'first_name'       => 'required|string|max:255',
            'middle_name'      => 'nullable|string|max:255',
            'last_name'        => 'required|string|max:255',
            'gender'           => 'required|in:male,female,other',
            'dob'              => 'required|date',
            'status'           => 'nullable|in:pending,active,inactive',
            // New validation rule for employment_type:
            'employment_type'  => 'required|in:regular,casual,project,seasonal,fixed-term,probationary',
            'current_address'  => 'required|string|max:255',
            'permanent_address'=> 'nullable|string|max:255',
            'father_name'      => 'nullable|string|max:255',
            'mother_name'      => 'nullable|string|max:255',
            'previous_company' => 'nullable|string|max:255',
            'job_title'        => 'nullable|string|max:255',
            'years_experience' => 'nullable|numeric|min:0',
            'nationality'      => 'nullable|string|max:255',
            'department_id'    => 'required|exists:departments,id',
            'designation_id'   => 'required|exists:designations,id',
            'schedule_id'      => 'nullable|exists:schedules,id',
            'fingerprint_id'   => 'nullable|string|unique:employees,fingerprint_id',
            'profile_picture'  => 'nullable|image|max:2048',
        ]);

        try {
            DB::beginTransaction();

            // 1) Create the User (pending status)
            $role = Role::where('name', $data['role'])->firstOrFail();
            $user = User::create([
                'name'     => "{$data['first_name']} {$data['last_name']}",
                'email'    => $data['email'],
                'password' => bcrypt($data['password']),
                'role_id'  => $role->id,
                'status'   => $data['status'] ?? 'pending',
            ]);

            // 2) Auto-generate employee_code
            $nextNum = (Employee::max('id') ?? 0) + 1;
            $code    = 'EMP' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

            // 3) Create the Employee
            $emp = new Employee([
                'employee_code'     => $code,
                'user_id'           => $user->id,
                'email'             => $data['email'],
                'first_name'        => $data['first_name'],
                'middle_name'       => $data['middle_name'] ?? null,
                'last_name'         => $data['last_name'],
                'name'              => "{$data['first_name']} {$data['last_name']}",
                'gender'            => $data['gender'],
                'dob'               => $data['dob'],
                'status'            => $data['status'] ?? 'active',
                'employment_type'   => $data['employment_type'], // NEW
                'current_address'   => $data['current_address'],
                'permanent_address' => $data['permanent_address'] ?? null,
                'father_name'       => $data['father_name'] ?? null,
                'mother_name'       => $data['mother_name'] ?? null,
                'previous_company'  => $data['previous_company'] ?? null,
                'job_title'         => $data['job_title'] ?? null,
                'years_experience'  => $data['years_experience'] ?? null,
                'nationality'       => $data['nationality'] ?? null,
                'department_id'     => $data['department_id'],
                'designation_id'    => $data['designation_id'],
                'schedule_id'       => $data['schedule_id'] ?? null,
                'fingerprint_id'    => $data['fingerprint_id'] ?? null,
            ]);

            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                $name = time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/profile_pictures'), $name);
                $emp->profile_picture = 'uploads/profile_pictures/' . $name;
            }

            $emp->save();

            // 4) Send welcome email
            Mail::to($user->email)
                ->send(new NewEmployeeAccountMail($user, $data['password']));

            DB::commit();

            return redirect()
                   ->route('employees.index')
                   ->with('success', "Employee {$code} created — awaiting approval.");
        }
        catch (ValidationException $ve) {
            DB::rollBack();
            throw $ve;
        }
        catch (\Exception $e) {
            DB::rollBack();
            Log::error("Employee store failed: {$e->getMessage()}\n{$e->getTraceAsString()}");
            return back()->withInput()->with('error', 'Failed to add employee.');
        }
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit($id)
    {
        $employee     = Employee::with(['user','department','designation','schedule'])
                                ->findOrFail($id);
        $departments  = Department::orderBy('name')->get();
        $designations = Designation::orderBy('name')->get();
        $schedules    = Schedule::orderBy('name')->get();
        $roles        = Role::pluck('name','name')->toArray();

        // List of employment types for the dropdown
        $employmentTypes = [
            'regular'      => 'Regular',
            'casual'       => 'Casual',
            'project'      => 'Project',
            'seasonal'     => 'Seasonal',
            'fixed-term'   => 'Fixed-term',
            'probationary' => 'Probationary',
        ];

        return view('employees.edit', compact(
            'employee','departments','designations','schedules','roles','employmentTypes'
        ));
    }

    /**
     * Update the specified employee (and its user).
     */
    public function update(Request $request, $id)
    {
        $employee = Employee::with('user')->findOrFail($id);

        $data = $request->validate([
            'email'            => 'required|email|unique:users,email,'  . $employee->user->id,
            'role'             => 'required|in:admin,hr,employee,supervisor,timekeeper',
            'status'           => 'required|in:pending,active,inactive',
            'first_name'       => 'required|string|max:255',
            'middle_name'      => 'nullable|string|max:255',
            'last_name'        => 'required|string|max:255',
            'gender'           => 'required|in:male,female,other',
            'dob'              => 'required|date',
            // New validation rule for employment_type:
            'employment_type'  => 'required|in:regular,casual,project,seasonal,fixed-term,probationary',
            'current_address'  => 'required|string|max:255',
            'permanent_address'=> 'nullable|string|max:255',
            'father_name'      => 'nullable|string|max:255',
            'mother_name'      => 'nullable|string|max:255',
            'previous_company' => 'nullable|string|max:255',
            'job_title'        => 'nullable|string|max:255',
            'years_experience' => 'nullable|numeric|min:0',
            'nationality'      => 'nullable|string|max:255',
            'department_id'    => 'required|exists:departments,id',
            'designation_id'   => 'required|exists:designations,id',
            'schedule_id'      => 'nullable|exists:schedules,id',
            'fingerprint_id'   => 'nullable|string|unique:employees,fingerprint_id,' . $employee->id,
            'profile_picture'  => 'nullable|image|max:2048',
            'password'         => 'nullable|min:8|confirmed',
        ]);

        try {
            DB::beginTransaction();

            // Update User
            $user = $employee->user;
            $user->fill([
                'email'   => $data['email'],
                'name'    => "{$data['first_name']} {$data['last_name']}",
                'role_id' => Role::where('name', $data['role'])->first()->id,
                'status'  => $data['status'],
            ]);
            if (!empty($data['password'])) {
                $user->password = bcrypt($data['password']);
            }
            $user->save();

            // Update Employee
            $employee->fill([
                'email'             => $data['email'],
                'first_name'        => $data['first_name'],
                'middle_name'       => $data['middle_name'] ?? null,
                'last_name'         => $data['last_name'],
                'name'              => "{$data['first_name']} {$data['last_name']}",
                'gender'            => $data['gender'],
                'dob'               => $data['dob'],
                'status'            => $data['status'],
                'employment_type'   => $data['employment_type'], // NEW
                'current_address'   => $data['current_address'],
                'permanent_address' => $data['permanent_address'] ?? null,
                'father_name'       => $data['father_name'] ?? null,
                'mother_name'       => $data['mother_name'] ?? null,
                'previous_company'  => $data['previous_company'] ?? null,
                'job_title'         => $data['job_title'] ?? null,
                'years_experience'  => $data['years_experience'] ?? null,
                'nationality'       => $data['nationality'] ?? null,
                'department_id'     => $data['department_id'],
                'designation_id'    => $data['designation_id'],
                'schedule_id'       => $data['schedule_id'] ?? null,
                'fingerprint_id'    => $data['fingerprint_id'] ?? null,
            ]);
            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                $name = time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/profile_pictures'), $name);
                $employee->profile_picture = 'uploads/profile_pictures/' . $name;
            }
            $employee->save();

            DB::commit();

            return redirect()
                   ->route('employees.index')
                   ->with('success', 'Employee updated successfully.');
        }
        catch (ValidationException $ve) {
            DB::rollBack();
            throw $ve;
        }
        catch (\Exception $e) {
            DB::rollBack();
            Log::error("Employee update failed: {$e->getMessage()}\n{$e->getTraceAsString()}");
            return back()->withInput()->with('error', 'Failed to update employee.');
        }
    }

    /**
     * Remove the specified employee.
     */
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return redirect()
               ->route('employees.index')
               ->with('success', 'Employee deleted.');
    }
}
