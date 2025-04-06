<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display a listing of all attendance records.
     */
    public function index()
    {
        // Eager load the employee relationship to display employee name
        $attendances = Attendance::with('employee')->orderBy('created_at', 'desc')->get();
        return view('attendance.index', compact('attendances'));
    }

    /**
     * Show the kiosk-style attendance log form.
     */
    public function logForm()
    {
        return view('attendance.log');
    }

    /**
     * Process the kiosk attendance log submission.
     *
     * The form uses Employee ID instead of name.
     */
    public function logAttendance(Request $request)
    {
        $request->validate([
            'attendance_type' => 'required|in:time_in,time_out',
            'employee_id'     => 'required|exists:employees,id',
        ]);

        $employeeId = $request->employee_id;

        if ($request->attendance_type === 'time_in') {
            // Check if the employee is already clocked in (i.e. an open record exists)
            $existing = Attendance::where('employee_id', $employeeId)
                                  ->whereNull('time_out')
                                  ->first();
            if ($existing) {
                return back()->with('error', 'This employee is already clocked in.');
            }
            // Record Time In
            $attendance = Attendance::create([
                'employee_id' => $employeeId,
                'time_in'     => Carbon::now(),
            ]);
            return back()->with('success', 'Time In recorded at ' . $attendance->time_in->format('h:i:s A'));
        } else {
            // Process Time Out: find the most recent open record
            $attendance = Attendance::where('employee_id', $employeeId)
                                    ->whereNull('time_out')
                                    ->orderBy('time_in', 'desc')
                                    ->first();
            if (!$attendance) {
                return back()->with('error', 'No open Time In record found for this employee.');
            }
            $attendance->update([
                'time_out' => Carbon::now(),
            ]);
            return back()->with('success', 'Time Out recorded at ' . $attendance->time_out->format('h:i:s A'));
        }
    }
}
