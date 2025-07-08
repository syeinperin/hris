<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\User;
use App\Models\LeaveRequest;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Announcement;
use App\Models\Loan;
use App\Models\PerformanceFormAssignment;
use App\Models\PerformanceEvaluation;

class DashboardController extends Controller
{
    public function index()
    {
        $today  = Carbon::today();
        $cutoff = $today->copy()->addDays(7);

        // HR counts
        $employeeCount    = Employee::count();
        $pendingUserCount = User::where('status','pending')->count();
        $absentCount      = Employee::whereNotIn(
            'id',
            Attendance::whereDate('time_in', $today)->pluck('employee_id')
        )->count();
        $endingCount      = Employee::whereIn('employment_type',['probationary','fixed-term'])
            ->whereBetween('employment_end_date', [$today, $cutoff])->count();
        $loanEndingCount  = Loan::where('status','active')
            ->whereBetween('next_payment_date', [$today, $cutoff])->count();

        // Supervisor counts
        $pendingLeaveCount = LeaveRequest::where('status','pending')->count();

        $allAssign = PerformanceFormAssignment::with('form','employee.user')
            ->where('evaluator_id', auth()->id())
            ->get();
        $ongoing = $allAssign->filter(function($a) use ($today) {
            $notSubmitted = ! PerformanceEvaluation::where([
                'form_id'      => $a->form_id,
                'employee_id'  => $a->employee_id,
                'evaluator_id' => auth()->id(),
            ])->exists();

            $inWindow = (!$a->starts_at || $today->gte($a->starts_at))
                     && (!$a->ends_at   || $today->lte($a->ends_at));

            return $notSubmitted && $inWindow;
        });

        // Announcements + reminders
        $announcements = Announcement::latest()->take(5)->get();

        $day0 = $today->dayOfYear;
        $day7 = $cutoff->dayOfYear;
        if ($day7 < $day0) {
            $birthdays = Employee::whereNotNull('dob')
                ->where(function($q) use($day0,$day7){
                    $q->whereRaw('DAYOFYEAR(dob)>=?',[$day0])
                      ->orWhereRaw('DAYOFYEAR(dob)<=?',[$day7]);
                })
                ->orderByRaw('DAYOFYEAR(dob)')->get();
            $anniversaries = Employee::whereNotNull('employment_start_date')
                ->where(function($q) use($day0,$day7){
                    $q->whereRaw('DAYOFYEAR(employment_start_date)>=?',[$day0])
                      ->orWhereRaw('DAYOFYEAR(employment_start_date)<=?',[$day7]);
                })
                ->orderByRaw('DAYOFYEAR(employment_start_date)')->get();
        } else {
            $birthdays = Employee::whereNotNull('dob')
                ->whereRaw('DAYOFYEAR(dob) BETWEEN ? AND ?',[$day0,$day7])
                ->orderByRaw('DAYOFYEAR(dob)')->get();
            $anniversaries = Employee::whereNotNull('employment_start_date')
                ->whereRaw('DAYOFYEAR(employment_start_date) BETWEEN ? AND ?',[$day0,$day7])
                ->orderByRaw('DAYOFYEAR(employment_start_date)')->get();
        }
        $anniversaries = $anniversaries->map(function($e) use($today){
            $start = $e->employment_start_date;
            $years = $today->year - $start->year;
            if ($today->lt($start->copy()->year($today->year))) {
                $years--;
            }
            $e->service_years = max($years,0);
            return $e;
        });

        return view('dashboard', compact(
            'employeeCount',
            'pendingUserCount',
            'absentCount',
            'endingCount',
            'loanEndingCount',
            'pendingLeaveCount',
            'ongoing',
            'announcements',
            'birthdays',
            'anniversaries'
        ));
    }
}
