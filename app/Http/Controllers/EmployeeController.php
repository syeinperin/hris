<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\Employee;
use App\Models\User;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Schedule;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    /**
     * Active employees list.
     */
    public function index(Request $request)
    {
        $employees = Employee::with(['user','department','designation','schedule'])
            ->active()
            ->department($request->input('department_id'))
            ->type($request->input('employment_type'))
            ->search($request->input('search'))
            ->orderBy('id','asc')
            ->paginate(10)
            ->withQueryString();

        $inactiveCount = Employee::where('status','inactive')->count();

        $today      = Carbon::today();
        $weekAway   = $today->copy()->addDays(7);
        $endingCount = Employee::active()
            ->whereNotNull('employment_end_date')
            ->whereBetween('employment_end_date', [$today, $weekAway])
            ->count();

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
        $roles           = Role::pluck('name','name')->toArray();
        $designations    = Designation::orderBy('name')->pluck('name','id')->toArray();
        $schedules       = Schedule::orderBy('name')->pluck('name','id')->toArray();

        return view('employees.index', compact(
            'employees',
            'departments',
            'employmentTypes',
            'roles',
            'designations',
            'schedules',
            'inactiveCount',
            'endingCount'
        ));
    }

    /**
     * Inactive employees list.
     */
    public function inactive(Request $request)
    {
        $employees = Employee::with(['user','department','designation','schedule'])
            ->where('status','inactive')
            ->department($request->input('department_id'))
            ->type($request->input('employment_type'))
            ->search($request->input('search'))
            ->orderBy('id','asc')
            ->paginate(10)
            ->withQueryString();

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

        return view('employees.inactive', compact(
            'employees',
            'departments',
            'employmentTypes'
        ));
    }

    /**
     * Show create form.
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
            'departments',
            'designations',
            'schedules',
            'roles',
            'employmentTypes'
        ));
    }

    /**
     * Store new employee + user.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'email'                => 'required|email|unique:users,email',
            'password'             => 'required|min:8|confirmed',
            'role'                 => 'required|in:hr,supervisor,employee',
            'first_name'           => 'required|string|max:255',
            'middle_name'          => 'nullable|string|max:255',
            'last_name'            => 'required|string|max:255',
            'gender'               => 'required|in:male,female,other',
            'dob'                  => 'required|date',
            'status'               => 'nullable|in:pending,active,inactive',
            'employment_type'      => 'required|in:regular,casual,project,seasonal,fixed-term,probationary',
            'employment_start_date'=> 'nullable|date',
            'employment_end_date'  => 'required|date|after_or_equal:today',
            'current_address'      => 'required|string|max:255',
            'permanent_address'    => 'nullable|string|max:255',
            'father_name'          => 'nullable|string|max:255',
            'mother_name'          => 'nullable|string|max:255',
            'previous_company'     => 'nullable|string|max:255',
            'job_title'            => 'nullable|string|max:255',
            'years_experience'     => 'nullable|numeric|min:0',
            'nationality'          => 'nullable|string|max:255',
            'department_id'        => 'required|exists:departments,id',
            'designation_id'       => 'required|exists:designations,id',
            'schedule_id'          => 'nullable|exists:schedules,id',
            'fingerprint_id'       => 'nullable|string|unique:employees,fingerprint_id',
            'profile_picture'      => 'nullable|image|max:2048',
            // Benefits
            'gsis_id_no'           => 'nullable|string|max:255',
            'pagibig_id_no'        => 'nullable|string|max:255',
            'philhealth_tin_id_no' => 'nullable|string|max:255',
            'sss_no'               => 'nullable|string|max:255',
            'tin_no'               => 'nullable|string|max:255',
            'agency_employee_no'   => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $roleModel = Role::where('name', $data['role'])->firstOrFail();
            $user = User::create([
                'name'     => "{$data['first_name']} {$data['last_name']}",
                'email'    => $data['email'],
                'password' => bcrypt($data['password']),
                'role_id'  => $roleModel->id,
                'status'   => $data['status'] ?? 'pending',
            ]);

            $code = 'EMP' . str_pad((Employee::max('id') ?? 0) + 1, 3, '0', STR_PAD_LEFT);

            $employee = new Employee([
                'employee_code'         => $code,
                'user_id'               => $user->id,
                'email'                 => $data['email'],
                'first_name'            => $data['first_name'],
                'middle_name'           => $data['middle_name'] ?? null,
                'last_name'             => $data['last_name'],
                'name'                  => "{$data['first_name']} {$data['last_name']}",
                'gender'                => $data['gender'],
                'dob'                   => $data['dob'],
                'status'                => $data['status'] ?? 'active',
                'employment_type'       => $data['employment_type'],
                'employment_start_date' => $data['employment_start_date'] ?? $data['employment_end_date'],
                'employment_end_date'   => $data['employment_end_date'],
                'current_address'       => $data['current_address'],
                'permanent_address'     => $data['permanent_address'] ?? null,
                'father_name'           => $data['father_name'] ?? null,
                'mother_name'           => $data['mother_name'] ?? null,
                'previous_company'      => $data['previous_company'] ?? null,
                'job_title'             => $data['job_title'] ?? null,
                'years_experience'      => $data['years_experience'] ?? null,
                'nationality'           => $data['nationality'] ?? null,
                'department_id'         => $data['department_id'],
                'designation_id'        => $data['designation_id'],
                'schedule_id'           => $data['schedule_id'] ?? null,
                'fingerprint_id'        => $data['fingerprint_id'] ?? null,
                // Benefits
                'gsis_id_no'            => $data['gsis_id_no'] ?? null,
                'pagibig_id_no'         => $data['pagibig_id_no'] ?? null,
                'philhealth_tin_id_no'  => $data['philhealth_tin_id_no'] ?? null,
                'sss_no'                => $data['sss_no'] ?? null,
                'tin_no'                => $data['tin_no'] ?? null,
                'agency_employee_no'    => $data['agency_employee_no'] ?? null,
            ]);

            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                $name = time().'.'.$file->getClientOriginalExtension();
                $file->move(public_path('uploads/profile_pictures'), $name);
                $employee->profile_picture = 'uploads/profile_pictures/'.$name;
            }

            $employee->save();
            DB::commit();

            return redirect()
                   ->route('employees.index')
                   ->with('success', "Employee {$code} created.");
        }
        catch (ValidationException $ve) {
            DB::rollBack();
            throw $ve;
        }
        catch (\Exception $e) {
            DB::rollBack();
            Log::error("Employee store failed: {$e->getMessage()}");
            return back()->withInput()->with('error', 'Failed to add employee.');
        }
    }

    /**
     * Show edit form.
     */
    public function edit($id)
    {
        $employee        = Employee::with(['user','department','designation','schedule'])->findOrFail($id);
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

        return view('employees.edit', compact(
            'employee',
            'departments',
            'designations',
            'schedules',
            'roles',
            'employmentTypes'
        ));
    }

    /**
     * Update employee + user, then redirect to the employee list.
     */
     public function update(Request $request, $id)
    {
        $employee = Employee::with('user')->findOrFail($id);
        $user     = $employee->user;

        $data = $request->validate([
            'email'                => 'required|email|unique:users,email,'.$user->id,
            'role'                 => 'required|in:hr,supervisor,employee',
            'status'               => 'required|in:pending,active,inactive',
            'first_name'           => 'required|string|max:255',
            'middle_name'          => 'nullable|string|max:255',
            'last_name'            => 'required|string|max:255',
            'gender'               => 'required|in:male,female,other',
            'dob'                  => 'required|date',
            'employment_type'      => 'required|in:regular,casual,project,seasonal,fixed-term,probationary',
            'employment_start_date'=> 'nullable|date|before:employment_end_date',
            'employment_end_date'  => 'required|date|after:today',
            'current_address'      => 'required|string|max:255',
            'permanent_address'    => 'nullable|string|max:255',
            'father_name'          => 'nullable|string|max:255',
            'mother_name'          => 'nullable|string|max:255',
            'previous_company'     => 'nullable|string|max:255',
            'job_title'            => 'nullable|string|max:255',
            'years_experience'     => 'nullable|numeric|min:0',
            'nationality'          => 'nullable|string|max:255',
            'department_id'        => 'required|exists:departments,id',
            'designation_id'       => 'required|exists:designations,id',
            'schedule_id'          => 'nullable|exists:schedules,id',
            'fingerprint_id'       => 'nullable|string|unique:employees,fingerprint_id,'.$employee->id,
            'profile_picture'      => 'nullable|image|max:2048',
            // Benefits
            'gsis_id_no'           => 'nullable|string|max:255',
            'pagibig_id_no'        => 'nullable|string|max:255',
            'philhealth_tin_id_no' => 'nullable|string|max:255',
            'sss_no'               => 'nullable|string|max:255',
            'tin_no'               => 'nullable|string|max:255',
            'agency_employee_no'   => 'nullable|string|max:255',
            // *Password* (new!)
            'password'             => 'nullable|min:8|confirmed',
        ]);

        DB::beginTransaction();

        try {
            //
            // 1) UPDATE THE USER
            //
            $user->email   = $data['email'];
            $user->name    = "{$data['first_name']} {$data['last_name']}";
            $user->role_id = Role::where('name', $data['role'])->first()->id;
            $user->status  = $data['status'];

            if (! empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }

            $user->save();

            //
            // 2) UPDATE THE EMPLOYEE
            //
            $employee->fill(array_merge($data, [
                'name' => "{$data['first_name']} {$data['last_name']}"
            ]));

            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                $name = time().'.'.$file->getClientOriginalExtension();
                $file->move(public_path('uploads/profile_pictures'), $name);
                $employee->profile_picture = 'uploads/profile_pictures/'.$name;
            }

            $employee->save();

            DB::commit();

            return redirect()
                   ->route('employees.index')
                   ->with('success', 'Employee updated.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Employee update failed: {$e->getMessage()}");
            return redirect()
                   ->route('employees.index')
                   ->with('error', 'Failed to update employee.');
        }
    }

    /**
     * Mark active → inactive.
     */
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->update(['status' => 'inactive']);
        return back()->with('warning', "{$employee->employee_code} marked inactive.");
    }

    /**
     * Reject probation → inactive.
     */
    public function rejectProbation(Employee $employee)
    {
        $employee->update(['status' => 'inactive']);
        return back()->with('warning', "{$employee->employee_code} probation rejected.");
    }

    /**
     * Restore inactive → active.
     */
    public function restore(Employee $employee)
    {
        $employee->update(['status' => 'active']);
        return back()->with('success', "{$employee->employee_code} restored.");
    }

    /**
     * Ending Soon (with extensions).
     */
    public function endings(Request $request)
    {
        $today    = Carbon::today()->toDateString();
        $weekAway = Carbon::today()->addDays(7)->toDateString();

        $query = Employee::with(['department','designation','schedule'])
                         ->when(!$request->input('employment_type'), fn($q) =>
                             $q->whereNotNull('employment_end_date')
                               ->whereBetween('employment_end_date', [$today, $weekAway])
                         )
                         ->when($request->input('employment_type'), fn($q) =>
                             $q->where('employment_type', $request->input('employment_type'))
                         );

        if ($dept = $request->input('department_id')) {
            $query->where('department_id', $dept);
        }

        $employees       = $query->orderBy('employment_end_date','asc')->paginate(10)->withQueryString();
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
        $actionMap       = config('hr.action_map');

        return view('employees.endings', compact(
            'employees',
            'departments',
            'employmentTypes',
            'actionMap'
        ));
    }

    /**
     * Shared date‐adjustment logic.
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