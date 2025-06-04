<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\User;
use App\Models\LeaveRequest;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Schedule;
use App\Models\AuditLog;
use App\Models\Attendance;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today      = Carbon::today();
        $presentIds = Attendance::whereDate('time_in', $today)
                                ->pluck('employee_id')
                                ->unique()
                                ->toArray();

        return view('dashboard', [
            'employeeCount'             => Employee::count(),
            'pendingApprovalsCount'     => User::where('status','pending')->count(),
            'pendingLeaveRequestsCount' => LeaveRequest::where('status','pending')->count(),
            'absentCount'               => Employee::whereNotIn('id', $presentIds)->count(),
            'departmentCount'           => Department::count(),
            'designationCount'          => Designation::count(),
            'scheduleCount'             => Schedule::count(),
            'logsCount'                 => AuditLog::count(),
        ]);
    }
}
