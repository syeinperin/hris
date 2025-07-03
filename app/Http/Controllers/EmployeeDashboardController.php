<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class EmployeeDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // eager‐load the nested relationship
        $user->loadMissing('employee.leaveAllocations.leaveType');

        if (! $user->employee) {
            return redirect()->route('dashboard')
                             ->with('error', 'Please complete your employee profile first.');
        }

        $employee = $user->employee;
        $today    = Carbon::today();

        // 1) Minutes worked today
        $minutesWorked = Attendance::where('employee_id', $employee->id)
            ->whereDate('time_in', $today)
            ->whereNotNull('time_out')
            ->get()
            ->sum(fn($att) =>
                Carbon::parse($att->time_in)
                      ->diffInMinutes(Carbon::parse($att->time_out))
            );
        $hoursWorked = round($minutesWorked / 60, 2);

        // 2) Absent today?
        $absentToday = ! Attendance::where('employee_id', $employee->id)
            ->whereDate('time_in', $today)
            ->exists();

        // 3) Pending leave requests
        $pendingLeaves = LeaveRequest::where('user_id', $user->id)
                             ->where('status', 'pending')
                             ->count();

        // 4) Last punch in/out
        $lastPunch = Attendance::where('employee_id', $employee->id)
            ->latest('time_in')
            ->first();

        // 5) Leave summary (this year)
        $year        = $today->year;
        $allocations = $employee
            ->leaveAllocations
            ->where('year', $year);

        return view('employees.dashboard', compact(
            'hoursWorked',
            'absentToday',
            'pendingLeaves',
            'lastPunch',
            'allocations',
            'year'
        ));
    }
}
