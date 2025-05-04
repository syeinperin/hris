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
        $user->loadMissing('employee');

        if (! $user->employee) {
            return redirect()->route('dashboard')
                             ->with('error', 'Please complete your employee profile first.');
        }

        $employee = $user->employee;
        $today    = Carbon::today();

        // 1) Sum up all minutes worked today
        $minutesWorked = Attendance::where('employee_id', $employee->id)
            ->whereDate('time_in', $today)
            ->whereNotNull('time_out')
            ->get()
            ->sum(fn($att) =>
                Carbon::parse($att->time_in)
                      ->diffInMinutes(Carbon::parse($att->time_out))
            );

        // 2) Convert to hours (decimal) and round to 2 places
        $hoursWorked = round($minutesWorked / 60, 2);

        // 3) Check if absent
        $absentToday = ! Attendance::where('employee_id', $employee->id)
            ->whereDate('time_in', $today)
            ->exists();

        // 4) Pending leave requests count
        $pendingLeaves = LeaveRequest::where('user_id', $user->id)
                             ->where('status', 'pending')
                             ->count();

        // 5) Last punch
        $lastPunch = Attendance::where('employee_id', $employee->id)
            ->latest('time_in')
            ->first();

        return view('employees.dashboard', compact(
            'employee',
            'hoursWorked',
            'absentToday',
            'pendingLeaves',
            'lastPunch'
        ));
    }
}
