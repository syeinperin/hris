<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\User;
use App\Models\Sidebar;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $employeeCount             = Employee::count();
        $pendingApprovalsCount     = User::where('status','pending')->count();
        $pendingLeaveRequestsCount = LeaveRequest::where('status','pending')->count();

        $today       = Carbon::today();
        $presentIds  = Attendance::whereDate('time_in',$today)
                                 ->pluck('employee_id')
                                 ->unique()
                                 ->toArray();
        $absentCount = Employee::whereNotIn('id',$presentIds)->count();

        $items = Sidebar::all();

        // ← load resources/views/dashboard.blade.php
        return view('dashboard', [
            'employeeCount'             => $employeeCount,
            'pendingApprovalsCount'     => $pendingApprovalsCount,
            'pendingLeaveRequestsCount' => $pendingLeaveRequestsCount,
            'absentCount'               => $absentCount,
            'items'                     => $items,
        ]);
    }
}