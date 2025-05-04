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
     * Display a listing of employees with filter & search.
     */
    public function index(Request $request)
    {
        $query = Employee::with(['user','department','designation','schedule']);

        if ($term = $request->input('search')) {
            $query->where(function($q) use ($term) {
                $q->where('name','like',"%{$term}%")
                  ->orWhere('employee_code','like',"%{$term}%")
                  ->orWhereHas('user', fn($u) =>
                      $u->where('email','like',"%{$term}%")
                  );
            });
        }

        if ($dept = $request->input('department_id')) {
            $query->where('department_id', $dept);
        }

        if ($desig = $request->input('designation_id')) {
            $query->where('designation_id', $desig);
        }

        $employees    = $query->paginate(10)->withQueryString();
        $departments  = Department::orderBy('name')->pluck('name','id')->toArray();
        $designations = Designation::orderBy('name')->pluck('name','id')->toArray();
        $schedules    = Schedule::orderBy('name')->pluck('name','id')->toArray();
        $roles        = Role::pluck('name','name')->toArray();

        return view('employees.index', compact(
            'employees','departments','designations','schedules','roles'
        ));
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create()
    {
        $departments  = Department::orderBy('name')->get();
        $designations = Designation::orderBy('name')->get();
        $schedules    = Schedule::orderBy('name')->get();
        $roles        = Role::pluck('name','name')->toArray();

        return view('employees.create', compact(
            'departments','designations','schedules','roles'
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

            // 1) Create the User
            $role = Role::where('name',$data['role'])->firstOrFail();
            $user = User::create([
                'name'     => "{$data['first_name']} {$data['last_name']}",
                'email'    => $data['email'],
                'password' => bcrypt($data['password']),
                'role_id'  => $role->id,
                'status'   => 'pending',
            ]);

            // 2) Auto-generate employee_code
            $nextNum = (Employee::max('id') ?? 0) + 1;
            $code    = 'EMP' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

            // 3) Create the Employee
            $emp = new Employee();
            $emp->employee_code     = $code;
            $emp->user_id           = $user->id;
            $emp->email             = $data['email'];
            $emp->first_name        = $data['first_name'];
            $emp->middle_name       = $data['middle_name'] ?? null;
            $emp->last_name         = $data['last_name'];
            $emp->name              = "{$data['first_name']} {$data['last_name']}";
            $emp->gender            = $data['gender'];
            $emp->dob               = $data['dob'];
            $emp->current_address   = $data['current_address'];
            $emp->permanent_address = $data['permanent_address'] ?? null;
            $emp->father_name       = $data['father_name'] ?? null;
            $emp->mother_name       = $data['mother_name'] ?? null;
            $emp->previous_company  = $data['previous_company'] ?? null;
            $emp->job_title         = $data['job_title'] ?? null;
            $emp->years_experience  = $data['years_experience'] ?? null;
            $emp->nationality       = $data['nationality'] ?? null;
            $emp->department_id     = $data['department_id'];
            $emp->designation_id    = $data['designation_id'];
            $emp->schedule_id       = $data['schedule_id'] ?? null;
            $emp->fingerprint_id    = $data['fingerprint_id'] ?? null;

            if ($request->hasFile('profile_picture')) {
                $file     = $request->file('profile_picture');
                $filename = time().'.'.$file->getClientOriginalExtension();
                $file->move(public_path('uploads/profile_pictures'), $filename);
                $emp->profile_picture = 'uploads/profile_pictures/'.$filename;
            }

            $emp->save();

            // 4) Send welcome email
            Mail::to($user->email)
                ->send(new NewEmployeeAccountMail($user, $data['password']));

            DB::commit();

            return redirect()
                   ->route('employees.index')
                   ->with('success',"Employee {$code} created — awaiting approval.");

        } catch (ValidationException $ve) {
            DB::rollBack();
            throw $ve;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Employee store failed: {$e->getMessage()}\n{$e->getTraceAsString()}");
            return back()->withInput()->with('error','Failed to add employee.');
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

        return view('employees.edit', compact(
            'employee','departments','designations','schedules','roles'
        ));
    }

    /**
     * Update the specified employee (and its user).
     */
    public function update(Request $request, $id)
    {
        $employee = Employee::with('user')->findOrFail($id);

        $data = $request->validate([
            'email'            => 'required|email|unique:users,email,' . $employee->user->id,
            'role'             => 'required|in:admin,hr,employee,supervisor,timekeeper',
            'status'           => 'required|in:pending,active,inactive',
            'first_name'       => 'required|string|max:255',
            'middle_name'      => 'nullable|string|max:255',
            'last_name'        => 'required|string|max:255',
            'gender'           => 'required|in:male,female,other',
            'dob'              => 'required|date',
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
            $user->email   = $data['email'];
            $user->name    = "{$data['first_name']} {$data['last_name']}";
            $user->role_id = Role::where('name',$data['role'])->first()->id;
            $user->status  = $data['status'];
            if (!empty($data['password'])) {
                $user->password = bcrypt($data['password']);
            }
            $user->save();

            // Update Employee
            $employee->email             = $data['email'];
            $employee->first_name        = $data['first_name'];
            $employee->middle_name       = $data['middle_name'] ?? null;
            $employee->last_name         = $data['last_name'];
            $employee->name              = "{$data['first_name']} {$data['last_name']}";
            $employee->gender            = $data['gender'];
            $employee->dob               = $data['dob'];
            $employee->current_address   = $data['current_address'];
            $employee->permanent_address = $data['permanent_address'] ?? null;
            $employee->father_name       = $data['father_name'] ?? null;
            $employee->mother_name       = $data['mother_name'] ?? null;
            $employee->previous_company  = $data['previous_company'] ?? null;
            $employee->job_title         = $data['job_title'] ?? null;
            $employee->years_experience  = $data['years_experience'] ?? null;
            $employee->nationality       = $data['nationality'] ?? null;
            $employee->department_id     = $data['department_id'];
            $employee->designation_id    = $data['designation_id'];
            $employee->schedule_id       = $data['schedule_id'] ?? null;
            $employee->fingerprint_id    = $data['fingerprint_id'] ?? null;

            if ($request->hasFile('profile_picture')) {
                $file     = $request->file('profile_picture');
                $filename = time().'.'.$file->getClientOriginalExtension();
                $file->move(public_path('uploads/profile_pictures'), $filename);
                $employee->profile_picture = 'uploads/profile_pictures/'.$filename;
            }

            $employee->save();

            DB::commit();

            return redirect()
                   ->route('employees.index')
                   ->with('success','Employee updated successfully.');

        } catch (ValidationException $ve) {
            DB::rollBack();
            throw $ve;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Employee update failed: {$e->getMessage()}\n{$e->getTraceAsString()}");
            return back()->withInput()->with('error','Failed to update employee.');
        }
    }

    /**
     * Remove the specified employee (and its user).
     */
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return redirect()
               ->route('employees.index')
               ->with('success','Employee deleted.');
    }
}