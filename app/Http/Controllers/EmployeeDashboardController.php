<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\LeaveAllocation;
use App\Models\LeaveType; // ← add
use Carbon\Carbon;

class EmployeeDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->status !== 'active') {
            Auth::logout();
            return redirect()->route('login')
                ->withErrors(['Your account is not yet approved.']);
        }

        if (! $user->employee) {
            return redirect()->route('dashboard')
                ->with('error', 'Please complete your employee profile first.');
        }

        $employee = $user->employee;
        $gender   = strtolower((string) $employee->gender);
        $today    = Carbon::today();

        // 1) Hours worked today
        $minutesWorked = Attendance::where('employee_id', $employee->id)
            ->whereDate('time_in', $today)
            ->whereNotNull('time_out')
            ->get()
            ->sum(function ($att) {
                return Carbon::parse($att->time_in)->diffInMinutes(Carbon::parse($att->time_out));
            });
        $hoursWorked = round($minutesWorked / 60, 2);

        // 2) Absent today?
        $absentToday = ! Attendance::where('employee_id', $employee->id)
            ->whereDate('time_in', $today)
            ->exists();

        // 3) Pending leave requests count
        $pendingLeaves = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        // 4) Last punch
        $lastPunch = Attendance::where('employee_id', $employee->id)
            ->latest('time_in')
            ->first();

        // 5) Leave summary for the current year
        $year = $today->year;

        // Ensure allocations exist (only for applicable types)
        $this->ensureAllocationsFor($employee->id, $year, $gender);

        // Load only applicable types for this gender
        $allocations = LeaveAllocation::with(['leaveType' => function ($q) {
                $q->select('id', 'key', 'name');
            }])
            ->where('employee_id', $employee->id)
            ->where('year', $year)
            ->whereHas('leaveType', function ($q) use ($gender) {
                if ($gender === 'male')   $q->where('key', '!=', 'maternity');
                if ($gender === 'female') $q->where('key', '!=', 'paternity');
            })
            ->get();

        return view('employees.dashboard', compact(
            'hoursWorked', 'absentToday', 'pendingLeaves',
            'lastPunch', 'allocations', 'year'
        ));
    }

    /**
     * Create default allocations from active leave types if missing.
     * Only types applicable to $gender are created.
     */
    private function ensureAllocationsFor(int $employeeId, int $year, string $gender): void
    {
        $types = LeaveType::where('is_active', true)
            ->when($gender === 'male',   fn($q) => $q->where('key', '!=', 'maternity'))
            ->when($gender === 'female', fn($q) => $q->where('key', '!=', 'paternity'))
            ->get(['id', 'default_days']);

        foreach ($types as $type) {
            LeaveAllocation::firstOrCreate(
                [
                    'leave_type_id' => $type->id,
                    'employee_id'   => $employeeId,
                    'year'          => $year,
                ],
                [
                    'days_allocated' => (int) ($type->default_days ?? 0),
                    'days_used'      => 0,
                ]
            );
        }
    }
}
