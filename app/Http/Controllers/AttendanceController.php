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
    public function index(Request $request)
    {
        // Build a query and eager load the employee relationship.
        $query = Attendance::with('employee');

        // 1. Search by Employee Name
        if ($request->filled('employee_name')) {
            $employeeName = $request->input('employee_name');
            $query->whereHas('employee', function($q) use ($employeeName) {
                $q->where('name', 'like', "%{$employeeName}%");
            });
        }

        // 2. Filter by Date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // 3. Sort the results
        // Allow sorting by 'employee_name', 'time_in', or 'time_out'
        $sortBy    = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        
        // For employee_name, we can’t directly sort in Eloquent so we use employee_id
        if ($sortBy === 'employee_name') {
            $sortBy = 'employee_id';
        }

        $attendances = $query->orderBy($sortBy, $sortOrder)->paginate(10);

        return view('attendance.index', compact('attendances'));
    }

    /**
     * Generate PDF for filtered or selected attendance records.
     */
    public function printPdf(Request $request)
    {
        // Option A: If checkboxes are used for selected IDs.
        $selectedIds = $request->input('selected_ids', []);

        // Option B: Reuse the same filters as index.
        $query = Attendance::with('employee');
        
        if ($request->filled('employee_name')) {
            $employeeName = $request->employee_name;
            $query->whereHas('employee', function($q) use ($employeeName) {
                $q->where('name', 'like', "%{$employeeName}%");
            });
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if (!empty($selectedIds)) {
            $query->whereIn('id', $selectedIds);
        }

        $attendances = $query->get();

        // Generate PDF view using a PDF library (for example, barryvdh/laravel-dompdf)
        $pdf = \PDF::loadView('attendance.pdf', compact('attendances'));
        return $pdf->download('attendance_records.pdf');
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
            // Check if the employee is already clocked in (i.e. an open record exists).
            $existing = Attendance::where('employee_id', $employeeId)
                                  ->whereNull('time_out')
                                  ->first();

            if ($existing) {
                return back()->with('error', 'This employee is already clocked in.');
            }

            // Record Time In.
            $attendance = Attendance::create([
                'employee_id' => $employeeId,
                'time_in'     => Carbon::now(),
            ]);

            return back()->with('success', 'Time In recorded at ' . Carbon::parse($attendance->time_in)->format('h:i:s A'));
        } else {
            // Process Time Out: find the most recent open record.
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

            return back()->with('success', 'Time Out recorded at ' . Carbon::parse($attendance->time_out)->format('h:i:s A'));
        }
    }

    /**
     * Delete an attendance record.
     */
    public function destroy($id)
    {
        // Find the attendance record or throw a 404 error.
        $attendance = Attendance::findOrFail($id);

        // Delete the record.
        $attendance->delete();

        return redirect()->route('attendance.index')
                         ->with('success', 'Attendance record deleted successfully!');
    }
}
