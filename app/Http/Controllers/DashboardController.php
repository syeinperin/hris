<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\User;
use App\Models\LeaveRequest;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Announcement;

class DashboardController extends Controller
{
    public function index()
    {
        $today  = Carbon::today();
        $cutoff = $today->copy()->addDays(7);

        // basic counts
        $employeeCount         = Employee::count();
        $pendingUserCount      = User::where('status', 'pending')->count();
        $pendingLeaveCount     = LeaveRequest::where('status', 'pending')->count();
        $departmentCount       = Department::count();
        $designationCount      = Designation::count();
        $scheduleCount         = Schedule::count();
        $logCount              = AuditLog::count();

        // absentees today
        $presentIds = Attendance::whereDate('time_in', $today)
            ->pluck('employee_id')
            ->unique()
            ->toArray();
        $absentCount = Employee::whereNotIn('id', $presentIds)->count();

        // contracts ending soon
        $endingCount = Employee::whereIn('employment_type', ['probationary','fixed-term'])
            ->whereBetween('employment_end_date', [
                $today->startOfDay(),
                $cutoff->endOfDay(),
            ])->count();

        // latest announcements
        $announcements = Announcement::latest()->take(5)->get();

        // upcoming birthdays & anniversaries
        $day0 = $today->dayOfYear;
        $day7 = $cutoff->dayOfYear;

        $birthdays = Employee::whereNotNull('dob')
            ->whereRaw('DAYOFYEAR(dob) BETWEEN ? AND ?', [$day0, $day7])
            ->orderByRaw('DAYOFYEAR(dob)')
            ->get();

        $anniversaries = Employee::whereNotNull('employment_start_date')
            ->whereRaw('DAYOFYEAR(employment_start_date) BETWEEN ? AND ?', [$day0, $day7])
            ->orderByRaw('DAYOFYEAR(employment_start_date)')
            ->get()
            ->map(fn($emp) => tap($emp, function($e) use ($today) {
                $start = $e->employment_start_date;
                $years = $today->year - $start->year;
                if ($today->lt($start->copy()->year($today->year))) {
                    $years--;
                }
                $e->service_years = max($years, 0);
            }));

        return view('dashboard', compact(
            'employeeCount','pendingUserCount','pendingLeaveCount',
            'departmentCount','designationCount','scheduleCount',
            'logCount','absentCount','endingCount',
            'announcements','birthdays','anniversaries'
        ));
    }
}
