<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\User;
use App\Models\LeaveRequest;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\Announcement;          // ← make sure this is here!
use Carbon\Carbon;

class AnnouncementController extends Controller
{
    public function index()
    {
        $today  = Carbon::today();
        $cutoff = $today->copy()->addDays(7);

        // … your existing counts …
        $employeeCount      = Employee::count();
        $pendingApprovals   = User::where('status','pending')->count();
        $pendingLeaves      = LeaveRequest::where('status','pending')->count();
        $presentIds         = Attendance::whereDate('time_in',$today)->pluck('employee_id');
        $absentCount        = Employee::whereNotIn('id',$presentIds)->count();
        $departmentCount    = Department::count();
        $designationCount   = Designation::count();
        $scheduleCount      = Schedule::count();
        $upcomingEndings    = Employee::whereIn('employment_type',['probationary','fixed-term'])
                                     ->whereBetween('employment_end_date', [$today, $cutoff])
                                     ->count();

        // ← NEW: pull the latest 5 announcements
        $latestAnnouncements = Announcement::latest()->take(5)->get();

        return view('dashboard', [
            'employeeCount'         => $employeeCount,
            'pendingApprovalsCount' => $pendingApprovals,
            'pendingLeaveRequests'  => $pendingLeaves,
            'absentCount'           => $absentCount,
            'departmentCount'       => $departmentCount,
            'designationCount'      => $designationCount,
            'scheduleCount'         => $scheduleCount,
            'upcomingEndingsCount'  => $upcomingEndings,
            'latestAnnouncements'   => $latestAnnouncements,   // ← pass it in
        ]);
    }
}
