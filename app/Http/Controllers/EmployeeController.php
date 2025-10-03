<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Employee;
use App\Models\User;
use App\Models\Approval;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Schedule;
use App\Models\FaceTemplate;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    /** Philippine provinces dropdown */
    protected array $philippineProvinces = [
        'Cavite','Laguna','Batangas','Rizal','Quezon',
    ];

    /* =========================================================
     * LISTINGS
     * ========================================================= */
    public function index(Request $request)
    {
        $employees = Employee::with(['user','department','designation','schedule'])
            ->active()
            ->department($request->department_id)
            ->type($request->employment_type)
            ->search($request->search)
            ->orderBy('id','asc')
            ->paginate(10)
            ->withQueryString();

        $inactiveCount = Employee::where('status','inactive')->count();

        $today    = Carbon::today();
        $weekAway = $today->copy()->addDays(7);
        $endingCount = Employee::whereIn('status', ['active','pending'])
            ->whereNotNull('employment_end_date')
            ->whereBetween('employment_end_date', [$today, $weekAway])
            ->count();

        $departments     = Department::orderBy('name')->pluck('name','id')->toArray();
        $employmentTypes = [
            ''=>'All Types','regular'=>'Regular','casual'=>'Casual','project'=>'Project',
            'seasonal'=>'Seasonal','fixed-term'=>'Fixed-Term','probationary'=>'Probationary',
        ];
        $roles        = Role::pluck('name','name')->toArray();
        $designations = Designation::orderBy('name')->pluck('name','id')->toArray();
        $schedules    = Schedule::orderBy('name')->pluck('name','id')->toArray();

        return view('employees.index', compact(
            'employees','departments','employmentTypes','roles','designations','schedules','inactiveCount','endingCount'
        ))->with('philippineProvinces', $this->philippineProvinces);
    }

    public function inactive(Request $request)
    {
        $inactiveCount = Employee::where('status','inactive')->count();
        $today    = Carbon::today();
        $weekAway = $today->copy()->addDays(7);
        $endingCount = Employee::active()
            ->whereNotNull('employment_end_date')
            ->whereBetween('employment_end_date', [$today, $weekAway])
            ->count();

        $employees = Employee::with(['user','department','designation','schedule'])
            ->inactive()
            ->department($request->department_id)
            ->type($request->employment_type)
            ->search($request->search)
            ->orderBy('id','asc')
            ->paginate(10)
            ->withQueryString();

        $departments     = Department::orderBy('name')->pluck('name','id')->toArray();
        $employmentTypes = [
            ''=>'All Types','regular'=>'Regular','casual'=>'Casual','project'=>'Project',
            'seasonal'=>'Seasonal','fixed-term'=>'Fixed-Term','probationary'=>'Probationary',
        ];
        $roles        = Role::pluck('name','name')->toArray();
        $designations = Designation::orderBy('name')->pluck('name','id')->toArray();
        $schedules    = Schedule::orderBy('name')->pluck('name','id')->toArray();

        return view('employees.index', compact(
            'employees','departments','employmentTypes','roles','designations','schedules','inactiveCount','endingCount'
        ))->with('philippineProvinces', $this->philippineProvinces);
    }

    /* =========================================================
     * CREATE + STORE
     * ========================================================= */
    public function create()
    {
        $departments     = Department::orderBy('name')->pluck('name','id')->toArray();
        $designations    = Designation::orderBy('name')->pluck('name','id')->toArray();
        $schedules       = Schedule::orderBy('name')->pluck('name','id')->toArray();
        $roles           = Role::pluck('name','name')->toArray();
        $employmentTypes = [
            'regular'=>'Regular','casual'=>'Casual','project'=>'Project',
            'seasonal'=>'Seasonal','fixed-term'=>'Fixed-Term','probationary'=>'Probationary',
        ];

        return view('employees.create', compact(
            'departments','designations','schedules','roles','employmentTypes'
        ))->with('philippineProvinces', $this->philippineProvinces);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            // Account
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:8|confirmed',
            'role'      => 'required|in:hr,supervisor,employee',

            // Personal
            'first_name' => 'required|string|max:255',
            'middle_name'=> 'nullable|string|max:255',
            'last_name'  => 'required|string|max:255',
            'gender'     => 'required|in:male,female,other',
            'dob'        => 'required|date|before:-18 years',
            'birth_place'=> 'nullable|string|max:255',
            'civil_status'=> 'nullable|in:single,married,widowed,separated,other',
            'profile_picture'        => 'nullable|image|max:2048',
            'profile_picture_camera' => 'nullable|string', // base64 from camera modal

            // Address
            'current_street_address' => 'required|string|max:255',
            'current_city'           => 'required|string|max:255',
            'current_province'       => 'required|string|max:255',
            'current_postal_code'    => 'nullable|string|max:20',
            'permanent_address'      => 'nullable|string|max:255',

            // Employment
            'employment_type' => 'required|in:regular,casual,project,seasonal,fixed-term,probationary',
            'employment_start_date' => 'required|date',
            'employment_end_date'   => 'nullable|date|after:employment_start_date',
            'department_id'  => 'required|exists:departments,id',
            'designation_id' => 'required|exists:designations,id',
            'schedule_id'    => 'nullable|exists:schedules,id',
            'fingerprint_id' => 'nullable|string|unique:employees,fingerprint_id',

            // Family / Background
            'religion' => 'nullable|string|max:255',
            'spouse'   => 'nullable|string|max:255',
            'occupation'=> 'nullable|string|max:255',
            'name_of_children'=> 'nullable|string|max:255',
            'children_birth_date'=> 'nullable|date',
            'father_name'   => 'nullable|string|max:255',
            'mother_name'   => 'nullable|string|max:255',
            'father_occupation'=> 'nullable|string|max:255',
            'mother_occupation'=> 'nullable|string|max:255',
            'languages_spoken'=> 'nullable|string',

            // Emergency Contact
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_address'=> 'nullable|string|max:255',
            'emergency_contact_phone'  => 'nullable|string|max:50',

            // Education
            'elementary_school' => 'nullable|string|max:255',
            'elementary_year_graduated'=> 'nullable|digits:4',
            'high_school'       => 'nullable|string|max:255',
            'high_school_year_graduated'=> 'nullable|digits:4',
            'college'           => 'nullable|string|max:255',
            'college_year_graduated'=> 'nullable|digits:4',
            'degree_received'   => 'nullable|string|max:255',

            // Misc
            'special_skills' => 'nullable|string',
            'height' => 'nullable|numeric',
            'weight' => 'nullable|numeric',

            // Face Recognition
            'face_descriptor'   => 'nullable|string',
            'face_image_base64' => 'nullable|string',

            // Documents on CREATE
            'resume_file'            => 'nullable|file|max:10240',
            'mdr_philhealth_file'    => 'nullable|file|max:10240',
            'mdr_sss_file'           => 'nullable|file|max:10240',
            'mdr_pagibig_file'       => 'nullable|file|max:10240',
            'medical_documents.*'    => 'nullable|file|max:10240',
        ]);

        DB::beginTransaction();
        try {
            // Create User
            $roleModel = Role::where('name',$data['role'])->firstOrFail();
            $user = User::create([
                'name' => "{$data['first_name']} {$data['last_name']}",
                'email'=> $data['email'],
                'password'=> Hash::make($data['password']),
                'status'=> 'pending',
                'role_id'=> $roleModel->id,
            ]);
            $user->assignRole($roleModel->name);

            // Generate Employee Code
            $nextId = (Employee::max('id') ?? 0) + 1;
            $code = 'EMP'.str_pad($nextId,4,'0',STR_PAD_LEFT);

            // Profile picture: prefer file; fallback to camera base64
            if ($request->hasFile('profile_picture')) {
                $data['profile_picture'] = $request->file('profile_picture')
                    ->store('uploads/profile_pictures','public');
                $data['profile_updated_at'] = now();
            } elseif (!empty($data['profile_picture_camera']) &&
                      str_starts_with($data['profile_picture_camera'], 'data:image/')) {
                $folder = 'uploads/profile_pictures';
                $filename = $code.'-'.now()->format('YmdHis').'.png';
                $binary = base64_decode(preg_replace('#^data:image/\w+;base64,#','', $data['profile_picture_camera']));
                Storage::disk('public')->put("$folder/$filename", $binary);
                $data['profile_picture'] = "$folder/$filename";
                $data['profile_updated_at'] = now();
            }

            // Single-file documents
            foreach ([
                'resume_file'         => 'uploads/resume',
                'mdr_philhealth_file' => 'uploads/mdr/philhealth',
                'mdr_sss_file'        => 'uploads/mdr/sss',
                'mdr_pagibig_file'    => 'uploads/mdr/pagibig',
            ] as $field => $dir) {
                if ($request->hasFile($field)) {
                    $data[$field] = $request->file($field)->store($dir, 'public');
                }
            }

            // Multiple medical documents
            $med = [];
            if ($request->hasFile('medical_documents')) {
                foreach ($request->file('medical_documents') as $file) {
                    if ($file) $med[] = $file->store('uploads/medical', 'public');
                }
            }
            if ($med) {
                // Model should cast to array; it will be stored as JSON
                $data['medical_documents'] = $med;
            }

            // Create Employee
            $employee = Employee::create(array_merge($data, [
                'employee_code' => $code,
                'user_id'       => $user->id,
                'name'          => "{$data['first_name']} {$data['last_name']}",
                'status'        => 'pending',
            ]));

            // Approval
            Approval::create([
                'approvable_type'=> User::class,
                'approvable_id'  => $user->id,
                'requested_by'   => auth()->id(),
                'status'         => 'pending',
            ]);

            // Optional FaceTemplate
            if (!empty($data['face_descriptor'])) {
                $desc = json_decode($data['face_descriptor'], true);
                $imagePath = null;
                if (!empty($data['face_image_base64']) && str_starts_with($data['face_image_base64'],'data:image/')) {
                    $folder = 'face-templates';
                    $filename = $code.'-'.now()->format('YmdHis').'.png';
                    $binary = base64_decode(preg_replace('#^data:image/\w+;base64,#','',$data['face_image_base64']));
                    Storage::disk('public')->put($folder.'/'.$filename,$binary);
                    $imagePath = $folder.'/'.$filename;
                }
                FaceTemplate::create([
                    'employee_id'=>$employee->id,
                    'descriptor'=>$desc,
                    'image_path'=>$imagePath,
                ]);
            }

            DB::commit();
            return redirect()->route('employees.index')->with('success',"Employee {$code} created.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Employee store failed: ".$e->getMessage());
            return back()->withInput()->with('error',"Failed to add employee: ".$e->getMessage());
        }
    }

    /* =========================================================
     * EDIT + UPDATE
     * ========================================================= */
    public function edit($id)
    {
        $employee = Employee::with(['user','department','designation','schedule'])->findOrFail($id);
        $departments     = Department::orderBy('name')->pluck('name','id')->toArray();
        $designations    = Designation::orderBy('name')->pluck('name','id')->toArray();
        $schedules       = Schedule::orderBy('name')->pluck('name','id')->toArray();
        $roles           = Role::pluck('name','name')->toArray();
        $employmentTypes = [
            'regular'=>'Regular','casual'=>'Casual','project'=>'Project',
            'seasonal'=>'Seasonal','fixed-term'=>'Fixed-Term','probationary'=>'Probationary',
        ];
        return view('employees.edit-modal', compact('employee','departments','designations','schedules','roles','employmentTypes'))
            ->with('philippineProvinces',$this->philippineProvinces);
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::with('user')->findOrFail($id);
        $user = $employee->user;

        $data = $request->validate([
            // Account
            'email'     => 'required|email|unique:users,email,'.$user->id,
            'password'  => 'nullable|min:8|confirmed',
            'role'      => 'required|in:hr,supervisor,employee',
            'status'    => 'required|in:pending,active,inactive',

            // Personal
            'first_name' => 'required|string|max:255',
            'middle_name'=> 'nullable|string|max:255',
            'last_name'  => 'required|string|max:255',
            'gender'     => 'required|in:male,female,other',
            'dob'        => 'required|date|before:-18 years',
            'birth_place'=> 'nullable|string|max:255',
            'civil_status'=> 'nullable|in:single,married,widowed,separated,other',
            'profile_picture'        => 'nullable|image|max:2048',
            'profile_picture_camera' => 'nullable|string',

            // Address
            'current_street_address' => 'required|string|max:255',
            'current_city'           => 'required|string|max:255',
            'current_province'       => 'required|string|max:255',
            'current_postal_code'    => 'nullable|string|max:20',
            'permanent_address'      => 'nullable|string|max:255',

            // Employment
            'employment_type' => 'required|in:regular,casual,project,seasonal,fixed-term,probationary',
            'employment_start_date' => 'required|date',
            'employment_end_date'   => 'nullable|date|after:employment_start_date',
            'department_id'  => 'required|exists:departments,id',
            'designation_id' => 'required|exists:designations,id',
            'schedule_id'    => 'nullable|exists:schedules,id',
            'fingerprint_id' => 'nullable|string|unique:employees,fingerprint_id,'.$employee->id,

            // Family / Background
            'religion' => 'nullable|string|max:255',
            'spouse'   => 'nullable|string|max:255',
            'occupation'=> 'nullable|string|max:255',
            'name_of_children'=> 'nullable|string|max:255',
            'children_birth_date'=> 'nullable|date',
            'father_name'   => 'nullable|string|max:255',
            'mother_name'   => 'nullable|string|max:255',
            'father_occupation'=> 'nullable|string|max:255',
            'mother_occupation'=> 'nullable|string|max:255',
            'languages_spoken'=> 'nullable|string',

            // Emergency Contact
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_address'=> 'nullable|string|max:255',
            'emergency_contact_phone'  => 'nullable|string|max:50',

            // Education
            'elementary_school' => 'nullable|string|max:255',
            'elementary_year_graduated'=> 'nullable|digits:4',
            'high_school'       => 'nullable|string|max:255',
            'high_school_year_graduated'=> 'nullable|digits:4',
            'college'           => 'nullable|string|max:255',
            'college_year_graduated'=> 'nullable|digits:4',
            'degree_received'   => 'nullable|string|max:255',

            // Misc
            'special_skills' => 'nullable|string',
            'height' => 'nullable|numeric',
            'weight' => 'nullable|numeric',

            // Documents (files are optional)
            'resume_file'         => 'nullable|file|max:10240',
            'mdr_philhealth_file' => 'nullable|file|max:10240',
            'mdr_sss_file'        => 'nullable|file|max:10240',
            'mdr_pagibig_file'    => 'nullable|file|max:10240',
            'medical_documents.*' => 'nullable|file|max:10240',
        ]);

        DB::transaction(function () use ($employee,$user,$data,$request) {
            // --- User / Role
            $roleModel = Role::where('name',$data['role'])->firstOrFail();
            $user->update([
                'name'   => $data['first_name'].' '.$data['last_name'],
                'email'  => $data['email'],
                'status' => $data['status'],
                'role_id'=> $roleModel->id,
            ]);
            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
                $user->save();
            }
            $user->syncRoles([$roleModel->name]);

            // --- Employee fields
            $empData = $data;
            unset($empData['email'],$empData['password'],$empData['role'],$empData['status']);

            // Profile picture: file upload first, else camera base64
            if ($request->hasFile('profile_picture')) {
                $empData['profile_picture'] = $request->file('profile_picture')
                    ->store('uploads/profile_pictures','public');
                $empData['profile_updated_at'] = now();
            } elseif (!empty($data['profile_picture_camera']) &&
                      str_starts_with($data['profile_picture_camera'], 'data:image/')) {
                $folder   = 'uploads/profile_pictures';
                $filename = $employee->employee_code.'-'.now()->format('YmdHis').'.png';
                $binary   = base64_decode(preg_replace('#^data:image/\w+;base64,#','', $data['profile_picture_camera']));
                Storage::disk('public')->put("$folder/$filename", $binary);
                $empData['profile_picture']    = "$folder/$filename";
                $empData['profile_updated_at'] = now();
            }

            // Single-file documents (replace if a new file is uploaded)
            foreach ([
                'resume_file'         => 'uploads/resume',
                'mdr_philhealth_file' => 'uploads/mdr/philhealth',
                'mdr_sss_file'        => 'uploads/mdr/sss',
                'mdr_pagibig_file'    => 'uploads/mdr/pagibig',
            ] as $field => $dir) {
                if ($request->hasFile($field)) {
                    $empData[$field] = $request->file($field)->store($dir, 'public');
                }
            }

            // Multiple medical documents – append to existing
            if ($request->hasFile('medical_documents')) {
                $existing = $employee->medical_documents ?: []; // cast to array in model
                $new = [];
                foreach ($request->file('medical_documents') as $file) {
                    if ($file) $new[] = $file->store('uploads/medical', 'public');
                }
                if ($new) {
                    $empData['medical_documents'] = array_values(array_unique(array_merge($existing, $new)));
                }
            }

            $employee->update($empData);
        });

        return redirect()->route('employees.index')->with('success',"{$employee->employee_code} updated successfully.");
    }

    /* =========================================================
     * STATUS + CONTRACT HANDLING
     * ========================================================= */
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->update(['status'=>'inactive']);
        return back()->with('warning',"{$employee->employee_code} marked inactive.");
    }

    public function restore(Employee $employee)
    {
        $employee->update(['status'=>'active']);
        return back()->with('success',"{$employee->employee_code} restored.");
    }

    public function rejectProbation(Employee $employee)
    {
        $employee->update(['status'=>'inactive']);
        return back()->with('warning',"{$employee->employee_code} probation rejected.");
    }

    public function endings(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $weekAway = Carbon::today()->addDays(7)->toDateString();

        $query = Employee::with(['department','designation','schedule'])
            ->when(!$request->employment_type, fn($q)=>$q->whereNotNull('employment_end_date')->whereBetween('employment_end_date',[$today,$weekAway]))
            ->when($request->employment_type, fn($q)=>$q->where('employment_type',$request->employment_type));

        if ($dept=$request->department_id) $query->where('department_id',$dept);

        $employees = $query->orderBy('employment_end_date','asc')->paginate(10)->withQueryString();
        $departments = Department::orderBy('name')->pluck('name','id')->toArray();
        $employmentTypes = [
            ''=>'All Types','regular'=>'Regular','casual'=>'Casual','project'=>'Project',
            'seasonal'=>'Seasonal','fixed-term'=>'Fixed-Term','probationary'=>'Probationary',
        ];
        $actionMap = config('hr.action_map');

        return view('employees.endings', compact('employees','departments','employmentTypes','actionMap'))
            ->with('philippineProvinces',$this->philippineProvinces);
    }

    protected function performDateAdjustment(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'new_start_date'=>'required|date|before:new_end_date',
            'new_end_date'=>'required|date|after:new_start_date',
        ]);
        $employee->update([
            'employment_start_date'=>$data['new_start_date'],
            'employment_end_date'=>$data['new_end_date'],
        ]);
        return back()->with('success',"{$employee->employee_code} dates updated.");
    }

    public function regularize(Employee $employee)
    {
        $employee->update([
            'employment_type'=>'regular',
            'employment_end_date'=>null,
        ]);
        return back()->with('success',"{$employee->employee_code} is now regular.");
    }

    public function extendTerm(Request $r, Employee $e){return $this->performDateAdjustment($r,$e);}
    public function extendSeason(Request $r, Employee $e){return $this->performDateAdjustment($r,$e);}
    public function extendProject(Request $r, Employee $e){return $this->performDateAdjustment($r,$e);}
    public function extendCasual(Request $r, Employee $e){return $this->performDateAdjustment($r,$e);}

    public function terminate(Request $r, Employee $e)
    {
        $e->delete();
        return back()->with('warning',"{$e->employee_code} terminated.");
    }

}
