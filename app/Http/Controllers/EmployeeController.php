<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Models\Employee;
use App\Models\User;
use App\Models\Approval;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Schedule;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    /**
     * List of Philippine provinces for address dropdown.
     */
    protected array $philippineProvinces = [
        'Cavite','Laguna','Batangas','Rizal','Quezon',
    ];

    /**
     * Display a listing of active employees.
     */
   /**
 * Display a listing of employees (active + pending).
 */
public function index(Request $request)
{
    // Fetch employees, include both active and pending statuses
    $employees = Employee::with(['user','department','designation','schedule'])
        ->active()
        ->department($request->department_id)
        ->type($request->employment_type)
        ->search($request->search)
        ->orderBy('id','asc')
        ->paginate(10)
        ->withQueryString();

    // Count inactives
    $inactiveCount = Employee::where('status','inactive')->count();

    // Count contracts ending soon among active employees
    $today    = Carbon::today();
    $weekAway = $today->copy()->addDays(7);
    $endingCount = Employee::whereIn('status', ['active','pending']) // or only active if you prefer
        ->whereNotNull('employment_end_date')
        ->whereBetween('employment_end_date', [$today, $weekAway])
        ->count();

    // Dropdown data
    $departments     = Department::orderBy('name')->pluck('name','id')->toArray();
    $employmentTypes = [
        ''             => 'All Types',
        'regular'      => 'Regular',
        'casual'       => 'Casual',
        'project'      => 'Project',
        'seasonal'     => 'Seasonal',
        'fixed-term'   => 'Fixed-Term',
        'probationary' => 'Probationary',
    ];
    $roles        = Role::pluck('name','name')->toArray();
    $designations = Designation::orderBy('name')->pluck('name','id')->toArray();
    $schedules    = Schedule::orderBy('name')->pluck('name','id')->toArray();

    return view('employees.index', [
        'employees'       => $employees,
        'departments'     => $departments,
        'employmentTypes' => $employmentTypes,
        'roles'           => $roles,
        'designations'    => $designations,
        'schedules'       => $schedules,
        'inactiveCount'   => $inactiveCount,
        'endingCount'     => $endingCount,
    ])->with('philippineProvinces', $this->philippineProvinces);
}

    public function inactive(Request $request)
{
    // 1) Counts for the header badges
    $inactiveCount = Employee::where('status','inactive')->count();

    $today     = Carbon::today();
    $weekAway  = $today->copy()->addDays(7);
    $endingCount = Employee::active()
        ->whereNotNull('employment_end_date')
        ->whereBetween('employment_end_date', [$today, $weekAway])
        ->count();

    // 2) The actual listing (inactive employees)
    $employees = Employee::with(['user','department','designation','schedule'])
        ->inactive()
        ->department($request->department_id)
        ->type($request->employment_type)
        ->search($request->search)
        ->orderBy('id','asc')
        ->paginate(10)
        ->withQueryString();

    // 3) Filters (same as index)
    $departments     = Department::orderBy('name')->pluck('name','id')->toArray();
    $employmentTypes = [
        ''             => 'All Types',
        'regular'      => 'Regular',
        'casual'       => 'Casual',
        'project'      => 'Project',
        'seasonal'     => 'Seasonal',
        'fixed-term'   => 'Fixed-Term',
        'probationary' => 'Probationary',
    ];
    $roles        = Role::pluck('name','name')->toArray();
    $designations = Designation::orderBy('name')->pluck('name','id')->toArray();
    $schedules    = Schedule::orderBy('name')->pluck('name','id')->toArray();

    // 4) Return the same view, passing both badge counts
    return view('employees.index', compact(
        'employees',
        'departments',
        'employmentTypes',
        'roles',
        'designations',
        'schedules',
        'inactiveCount',
        'endingCount'
    ))->with('philippineProvinces', $this->philippineProvinces);
}
    /**
     * Show the form for creating a new employee.
     */
    public function create()
    {
        $departments     = Department::orderBy('name')->get();
        $designations    = Designation::orderBy('name')->get();
        $schedules       = Schedule::orderBy('name')->get();
        $roles           = Role::pluck('name','name')->toArray();
        $employmentTypes = [
            'regular'      => 'Regular',
            'casual'       => 'Casual',
            'project'      => 'Project',
            'seasonal'     => 'Seasonal',
            'fixed-term'   => 'Fixed-Term',
            'probationary' => 'Probationary',
        ];

        return view('employees.create', compact(
            'departments','designations','schedules',
            'roles','employmentTypes'
        ))->with('philippineProvinces', $this->philippineProvinces);
    }

    /**
     * Store a newly created employee (and pending user) in storage.
     */
  public function store(Request $request)
{
    $data = $request->validate([
        // ── Account ─────────────────────────────────────────
        'email'    => 'required|email|unique:users,email',
        'password' => 'required|min:8|confirmed',
        'role'     => 'required|in:hr,supervisor,employee',

        // ── Personal ────────────────────────────────────────
        'first_name'  => 'required|string|max:255',
        'middle_name' => 'nullable|string|max:255',
        'last_name'   => 'required|string|max:255',
        'gender'      => 'required|in:male,female,other',
        'dob'         => 'required|date',

        // ── Employment ──────────────────────────────────────
        'employment_type'       => 'required|in:regular,casual,project,seasonal,fixed-term,probationary',
        'employment_start_date' => 'nullable|date',
        'employment_end_date'   => 'required|date|after_or_equal:today',

        // ── Address ─────────────────────────────────────────
        'current_street_address'=> 'required|string|max:255',
        'current_city'          => 'required|string|max:255',
        'current_province'      => 'required|string|in:'.implode(',',$this->philippineProvinces),
        'current_postal_code'   => 'nullable|string|max:20',
        'permanent_address'     => 'nullable|string|max:255',

        // ── Relations ───────────────────────────────────────
        'department_id'  => 'required|exists:departments,id',
        'designation_id' => 'required|exists:designations,id',
        'schedule_id'    => 'nullable|exists:schedules,id',
        'fingerprint_id' => 'nullable|string|unique:employees,fingerprint_id',
        'profile_picture'=> 'nullable|image|max:2048',

        // ── (plus your Benefits, Bio-Data, etc. fields…)  ///
    ]);

    DB::beginTransaction();

    try {
        // 1) Create the User (always pending)
        $roleModel = Role::where('name', $data['role'])->firstOrFail();
        $user = User::create([
            'name'     => "{$data['first_name']} {$data['last_name']}",
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'status'   => 'pending',            // ← fixed here
            'role_id'  => $roleModel->id,
        ]);
        $user->assignRole($roleModel->name);

        // 2) Create the Employee record (pending as well)
        $nextId = (Employee::max('id') ?? 0) + 1;
        $code   = 'EMP' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        $empData = array_merge($data, [
            'employee_code' => $code,
            'user_id'       => $user->id,
            'name'          => "{$data['first_name']} {$data['last_name']}",
            'status'        => 'pending',       // ← also pending
        ]);

        if ($request->hasFile('profile_picture')) {
            $empData['profile_picture'] = $request
                ->file('profile_picture')
                ->store('uploads/profile_pictures','public');
        }

        $employee = Employee::create($empData);

        // 3) Queue an approval for the new user
        Approval::create([
            'approvable_type' => User::class,
            'approvable_id'   => $user->id,
            'requested_by'    => auth()->id(),
            'status'          => 'pending',
        ]);

        DB::commit();

        return redirect()
            ->route('employees.index')
            ->with('success', "Employee {$code} created and pending approval.");

    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error("Employee store failed: {$e->getMessage()}");
        return back()
            ->withInput()
            ->with('error', 'Failed to add employee: ' . $e->getMessage());
    }
}

    /**
     * Show the form for editing the specified employee.
     */
    public function edit($id)
    {
        $employee        = Employee::with(['user','department','designation','schedule'])
                            ->findOrFail($id);
        $departments     = Department::orderBy('name')->get();
        $designations    = Designation::orderBy('name')->get();
        $schedules       = Schedule::orderBy('name')->get();
        $roles           = Role::pluck('name','name')->toArray();
        $employmentTypes = [
            'regular'=>'Regular','casual'=>'Casual','project'=>'Project',
            'seasonal'=>'Seasonal','fixed-term'=>'Fixed-Term','probationary'=>'Probationary',
        ];

        return view('employees.edit', compact(
            'employee','departments','designations','schedules','roles','employmentTypes'
        ))->with('philippineProvinces', $this->philippineProvinces);
    }

/**
 * Update the specified employee in storage.
 */
public function update(Request $request, $id)
{
    $employee = Employee::with('user')->findOrFail($id);
    $user     = $employee->user;

    $data = $request->validate([
        // Account
        'email'    => 'required|email|unique:users,email,'.$user->id,
        'password' => 'nullable|min:8|confirmed',
        'role'     => 'required|in:hr,supervisor,employee',
        'status'   => 'required|in:pending,active,inactive',

        // Personal
        'first_name'  => 'required|string|max:255',
        'middle_name' => 'nullable|string|max:255',
        'last_name'   => 'required|string|max:255',
        'gender'      => 'required|in:male,female,other',
        'dob'         => 'required|date',

        // Address
        'current_street_address' => 'required|string|max:255',
        'current_province'       => 'required|string|in:'.implode(',', $this->philippineProvinces),
        'current_city'           => 'required|string|max:255',
        'current_postal_code'    => 'nullable|string|max:20',
        'permanent_address'      => 'nullable|string|max:255',
        'birth_place'            => 'nullable|string|max:255',
        'civil_status'           => 'nullable|in:single,married,widowed,separated,other',

        // Work
        'employment_type'       => 'required|in:regular,casual,project,seasonal,fixed-term,probationary',
        'employment_start_date' => 'nullable|date',
        'employment_end_date'   => 'required|date|after_or_equal:employment_start_date',
        'department_id'         => 'required|exists:departments,id',
        'designation_id'        => 'required|exists:designations,id',
        'schedule_id'           => 'nullable|exists:schedules,id',
        'fingerprint_id'        => 'nullable|string|unique:employees,fingerprint_id,'.$employee->id,

        // File
        'profile_picture' => 'nullable|image|max:2048',

        // Optional extras
        'gsis_id_no'                 => 'nullable|string|max:255',
        'pagibig_id_no'              => 'nullable|string|max:255',
        'philhealth_tin_id_no'       => 'nullable|string|max:255',
        'sss_no'                     => 'nullable|string|max:255',
        'tin_no'                     => 'nullable|string|max:255',
        'agency_employee_no'         => 'nullable|string|max:255',
        'position_desired'           => 'nullable|string|max:255',
        'application_date'           => 'nullable|date',
        'city_address'               => 'nullable|string|max:255',
        'provincial_address'         => 'nullable|string|max:255',
        'telephone'                  => 'nullable|string|max:50',
        'cellphone'                  => 'nullable|string|max:50',
        'citizenship'                => 'nullable|string|max:255',
        'height'                     => 'nullable|numeric',
        'weight'                     => 'nullable|numeric',
        'religion'                   => 'nullable|string|max:255',
        'spouse'                     => 'nullable|string|max:255',
        'occupation'                 => 'nullable|string|max:255',
        'name_of_children'           => 'nullable|string|max:255',
        'children_birth_date'        => 'nullable|date',
        'father_name'                => 'nullable|string|max:255',
        'mother_name'                => 'nullable|string|max:255',
        'father_occupation'          => 'nullable|string|max:255',
        'mother_occupation'          => 'nullable|string|max:255',
        'languages_spoken'           => 'nullable|string',
        'emergency_contact_name'     => 'nullable|string|max:255',
        'emergency_contact_address'  => 'nullable|string|max:255',
        'emergency_contact_phone'    => 'nullable|string|max:50',
        'elementary_school'          => 'nullable|string|max:255',
        'elementary_year_graduated'  => 'nullable|digits:4',
        'high_school'                => 'nullable|string|max:255',
        'high_school_year_graduated' => 'nullable|digits:4',
        'college'                    => 'nullable|string|max:255',
        'college_year_graduated'     => 'nullable|digits:4',
        'degree_received'            => 'nullable|string|max:255',
        'special_skills'             => 'nullable|string',
        'emp1_company'               => 'nullable|string|max:255',
        'emp1_position'              => 'nullable|string|max:255',
        'emp1_from'                  => 'nullable|date',
        'emp1_to'                    => 'nullable|date|after_or_equal:emp1_from',
        'emp2_company'               => 'nullable|string|max:255',
        'emp2_position'              => 'nullable|string|max:255',
        'emp2_from'                  => 'nullable|date',
        'emp2_to'                    => 'nullable|date|after_or_equal:emp2_from',
        'char1_name'                 => 'nullable|string|max:255',
        'char1_position'             => 'nullable|string|max:255',
        'char1_company'              => 'nullable|string|max:255',
        'char1_contact'              => 'nullable|string|max:50',
        'char2_name'                 => 'nullable|string|max:255',
        'char2_position'             => 'nullable|string|max:255',
        'char2_company'              => 'nullable|string|max:255',
        'char2_contact'              => 'nullable|string|max:50',
        'res_cert_no'                => 'nullable|string|max:255',
        'res_cert_issued_at'         => 'nullable|string|max:255',
        'res_cert_issued_on'         => 'nullable|date',
        'nbi_no'                     => 'nullable|string|max:255',
        'passport_no'                => 'nullable|string|max:255',
    ]);

    DB::transaction(function () use ($data, $request, $employee, $user) {
        // Update USER
        $roleModel = \Spatie\Permission\Models\Role::where('name', $data['role'])->firstOrFail();

        $user->fill([
            'name'    => "{$data['first_name']} {$data['last_name']}",
            'email'   => $data['email'],
            'status'  => $data['status'],
            'role_id' => $roleModel->id,
        ]);
        if (!empty($data['password'])) {
            $user->password = \Illuminate\Support\Facades\Hash::make($data['password']);
        }
        $user->save();
        $user->syncRoles([$roleModel->name]);

        // Update EMPLOYEE
        $empData = $data;
        unset(
            $empData['email'],
            $empData['password'],
            $empData['password_confirmation'],
            $empData['role'],
            $empData['status']
        );

        $empData['name'] = "{$data['first_name']} {$data['last_name']}";

        if ($request->hasFile('profile_picture')) {
            $empData['profile_picture'] = $request->file('profile_picture')
                ->store('uploads/profile_pictures', 'public');
        }

        $employee->update($empData);
    });

    return redirect()
        ->route('employees.index')
        ->with('success', "{$employee->employee_code} updated successfully.");
}

    /**
     * Mark an employee as inactive.
     */
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->update(['status' => 'inactive']);
        return back()->with('warning', "{$employee->employee_code} marked inactive.");
    }

    /**
     * Reject probation (mark inactive).
     */
    public function rejectProbation(Employee $employee)
    {
        $employee->update(['status' => 'inactive']);
        return back()->with('warning', "{$employee->employee_code} probation rejected.");
    }

    /**
     * Restore an inactive employee.
     */
    public function restore(Employee $employee)
    {
        $employee->update(['status' => 'active']);
        return back()->with('success', "{$employee->employee_code} restored.");
    }

    /**
     * List employees whose contracts are ending soon.
     */
    public function endings(Request $request)
    {
        $today    = Carbon::today()->toDateString();
        $weekAway = Carbon::today()->addDays(7)->toDateString();

        $query = Employee::with(['department','designation','schedule'])
            ->when(!$request->employment_type, fn($q) =>
                $q->whereNotNull('employment_end_date')
                  ->whereBetween('employment_end_date', [$today, $weekAway])
            )
            ->when($request->employment_type, fn($q) =>
                $q->where('employment_type', $request->employment_type)
            );

        if ($dept = $request->department_id) {
            $query->where('department_id', $dept);
        }

        $employees       = $query->orderBy('employment_end_date','asc')
                                 ->paginate(10)
                                 ->withQueryString();
        $departments     = Department::orderBy('name')->pluck('name','id')->toArray();
        $employmentTypes = [
            ''=>'All Types','regular'=>'Regular','casual'=>'Casual',
            'project'=>'Project','seasonal'=>'Seasonal',
            'fixed-term'=>'Fixed-Term','probationary'=>'Probationary',
        ];
        $actionMap = config('hr.action_map');

        return view('employees.endings', compact(
            'employees','departments','employmentTypes','actionMap'
        ))->with('philippineProvinces', $this->philippineProvinces);
    }

    /**
     * Shared logic to extend or adjust contract dates.
     */
    protected function performDateAdjustment(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'new_start_date' => 'required|date|before:new_end_date',
            'new_end_date'   => 'required|date|after:new_start_date',
        ]);

        $employee->update([
            'employment_start_date' => $data['new_start_date'],
            'employment_end_date'   => $data['new_end_date'],
        ]);

        return back()->with('success', "{$employee->employee_code} dates updated.");
    }

    /**
     * Change employment type to regular.
     */
    public function regularize(Employee $employee)
    {
        $employee->update(['employment_type' => 'regular']);
        return back()->with('success', "{$employee->employee_code} is now regular.");
    }

    public function extendTerm(Request $request, Employee $employee)
    {
        return $this->performDateAdjustment($request, $employee);
    }

    public function terminate(Request $request, Employee $employee)
    {
        $employee->delete();
        return back()->with('warning', "{$employee->employee_code} terminated.");
    }

    public function extendSeason(Request $request, Employee $employee)
    {
        return $this->performDateAdjustment($request, $employee);
    }

    public function extendProject(Request $request, Employee $employee)
    {
        return $this->performDateAdjustment($request, $employee);
    }

    public function extendCasual(Request $request, Employee $employee)
    {
        return $this->performDateAdjustment($request, $employee);
    }
}