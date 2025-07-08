<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Schedule;

class ProfileController extends Controller
{
    public function edit()
    {
        $user     = Auth::user();
        $employee = Employee::where('user_id',$user->id)->firstOrFail();
        $isHr     = $user->role->name === 'hr';

        // only HR needs these dropdowns
        $departments  = Department::pluck('name','id');
        $designations = Designation::pluck('name','id');
        $schedules    = Schedule::pluck('name','id');
        $employmentTypes = [
            'regular'      => 'Regular',
            'casual'       => 'Casual',
            'project'      => 'Project',
            'seasonal'     => 'Seasonal',
            'fixed-term'   => 'Fixed-term',
            'probationary' => 'Probationary',
        ];

        return view('profile.edit', compact(
            'user','employee','isHr',
            'departments','designations','schedules','employmentTypes'
        ));
    }

    public function update(Request $request)
    {
        $user     = Auth::user();
        $employee = Employee::where('user_id',$user->id)->firstOrFail();
        $isHr     = $user->role->name === 'hr';

        // 30-day lockout for non-HR
        if (! $isHr && $employee->profile_updated_at) {
            $days = Carbon::parse($employee->profile_updated_at)
                          ->diffInDays(now());
            if ($days < 30) {
                return back()
                    ->withErrors(['too_soon'=>"Wait ".(30-$days)." more days."])
                    ->withInput();
            }
        }

        // base rules
        $rules = [
            'email'             => ['required','email','max:255',Rule::unique('users')->ignore($user)],
            'password'          => ['nullable','min:8','confirmed'],
            'first_name'        => ['required','string','max:255'],
            'middle_name'       => ['nullable','string','max:255'],
            'last_name'         => ['required','string','max:255'],
            'gender'            => ['required','in:male,female,other'],
            'dob'               => ['required','date'],
            'current_address'   => ['required','string','max:255'],
            'permanent_address' => ['nullable','string','max:255'],
            'profile_picture'   => ['nullable','image','max:2048'],
        ];

        if ($isHr) {
            $rules = array_merge($rules, [
                'department_id'        => ['required','exists:departments,id'],
                'designation_id'       => ['required','exists:designations,id'],
                'schedule_id'          => ['nullable','exists:schedules,id'],
                'employment_type'      => ['required','in:regular,casual,project,seasonal,fixed-term,probationary'],
                'employment_start_date'=> ['required','date'],
                'employment_end_date'  => ['required','date'],
                'sss_no'               => ['nullable','string','max:50'],
                'pagibig_id_no'        => ['nullable','string','max:50'],
                'philhealth_tin_id_no' => ['nullable','string','max:50'],
                'previous_company'     => ['nullable','string','max:255'],
                'job_title'            => ['nullable','string','max:255'],
                'years_experience'     => ['nullable','numeric','min:0'],
                'nationality'          => ['nullable','string','max:255'],
                'fingerprint_id'       => ['nullable','string',Rule::unique('employees')->ignore($employee)],
            ]);
        }

        $data = $request->validate($rules);

        // update User
        $user->email = $data['email'];
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();  

        // prepare Employee
        $fill = [
            'first_name'        => $data['first_name'],
            'middle_name'       => $data['middle_name'] ?? null,
            'last_name'         => $data['last_name'],
            'name'              => "{$data['first_name']} {$data['last_name']}",
            'gender'            => $data['gender'],
            'dob'               => $data['dob'],
            'current_address'   => $data['current_address'],
            'permanent_address' => $data['permanent_address'] ?? null,
        ];

        if ($isHr) {
            $fill = array_merge($fill, [
                'department_id'        => $data['department_id'],
                'designation_id'       => $data['designation_id'],
                'schedule_id'          => $data['schedule_id'] ?? null,
                'employment_type'      => $data['employment_type'],
                'employment_start_date'=> $data['employment_start_date'],
                'employment_end_date'  => $data['employment_end_date'],
                'sss_no'               => $data['sss_no'] ?? null,
                'pagibig_id_no'        => $data['pagibig_id_no'] ?? null,
                'philhealth_tin_id_no' => $data['philhealth_tin_id_no'] ?? null,
                'previous_company'     => $data['previous_company'] ?? null,
                'job_title'            => $data['job_title'] ?? null,
                'years_experience'     => $data['years_experience'] ?? null,
                'nationality'          => $data['nationality'] ?? null,
                'fingerprint_id'       => $data['fingerprint_id'] ?? null,
            ]);
        }

        if ($request->hasFile('profile_picture')) {
            $fill['profile_picture'] = $request
                ->file('profile_picture')
                ->store('profiles','public');
        }

        $employee->fill($fill);
        $employee->profile_updated_at = now();
        $employee->save();

        return back()->with('success','Profile updated.');
    }
}