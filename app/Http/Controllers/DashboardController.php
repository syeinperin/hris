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
        $today       = Carbon::today();
        $weekFromNow = Carbon::today()->addDays(7);

        // ── Total Employees ──────────────────────────────────────────────────
        $employeeCount = Employee::count();

        // ── Pending Approvals (Users waiting for activation) ────────────────
        $pendingApprovalsCount = User::where('status', 'pending')->count();

        // ── Pending Leave Requests ─────────────────────────────────────────
        $pendingLeaveRequestsCount = LeaveRequest::where('status', 'pending')->count();

        // ── Absentees (Employees not present today) ────────────────────────
        $presentIds = Attendance::whereDate('time_in', $today)
                                ->pluck('employee_id')
                                ->unique()
                                ->toArray();

        $absentCount = Employee::whereNotIn('id', $presentIds)->count();

        // ── Core Counts for Dashboard Cards ─────────────────────────────────
        $departmentCount  = Department::count();
        $designationCount = Designation::count();
        $scheduleCount    = Schedule::count();
        $logsCount        = AuditLog::count();

        // ── Upcoming Probations (using employment_end_date) ─────────────────
        //
        // Count all “probationary” employees whose employment_end_date
        // is not null and falls between today 00:00:00 and a week from now 23:59:59.
        //
        $upcomingProbationCount = Employee::where('employment_type', 'probationary')
            ->whereNotNull('employment_end_date')
            ->whereBetween('employment_end_date', [
                $today->format('Y-m-d').' 00:00:00',
                $weekFromNow->format('Y-m-d').' 23:59:59'
            ])
            ->count();

        return view('dashboard', [
            'employeeCount'             => $employeeCount,
            'pendingApprovalsCount'     => $pendingApprovalsCount,
            'pendingLeaveRequestsCount' => $pendingLeaveRequestsCount,
            'absentCount'               => $absentCount,
            'departmentCount'           => $departmentCount,
            'designationCount'          => $designationCount,
            'scheduleCount'             => $scheduleCount,
            'logsCount'                 => $logsCount,
            'upcomingProbationCount'    => $upcomingProbationCount,
        ]);
    }
}
