<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display the kiosk form (GET).
     */
    public function logForm()
    {
        return view('attendance.log');
    }

    /**
     * Handle kiosk attendance submission (POST).
     */
    public function logAttendance(Request $request)
    {
        $request->validate([
            'attendance_type' => 'required|in:time_in,time_out',
            'employee_code'   => 'required|string',
        ]);

        // Find the employee by their unique employee_code
        $employee = Employee::where('employee_code', $request->employee_code)->first();

        if (!$employee) {
            return back()->with('error', 'Employee not found. Please check the code and try again.');
        }

        // If type is time_in, create or update an attendance record
        if ($request->attendance_type === 'time_in') {
            Attendance::create([
                'employee_id' => $employee->id, // store numeric ID internally
                'time_in'     => Carbon::now(),
            ]);

            return back()->with('success', 'Time In recorded at ' . Carbon::now()->format('h:i:s A'));
        } else {
            // For time_out, find the most recent attendance with no time_out
            $attendance = Attendance::where('employee_id', $employee->id)
                ->whereNull('time_out')
                ->latest('time_in')
                ->first();

            if (!$attendance) {
                return back()->with('error', 'No matching Time In record found. Cannot Time Out.');
            }

            $attendance->update(['time_out' => Carbon::now()]);

            return back()->with('success', 'Time Out recorded at ' . Carbon::now()->format('h:i:s A'));
        }
    }

    /**
     * Display attendance records (GET).
     */
    public function index(Request $request)
    {
        // Filter logic if any...
        // Eager-load the employee relationship
        $attendances = Attendance::with('employee', 'schedule')->latest()->paginate(10);
        return view('attendance.index', compact('attendances'));
    }

    // ... other methods like destroy(), printPdf(), etc.

    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->delete();
    
        return redirect()->route('attendance.index')
                         ->with('success', 'Attendance record deleted successfully!');
    }
}    