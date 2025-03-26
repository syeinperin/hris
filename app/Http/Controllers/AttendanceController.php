<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    public function registerFingerprint(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        $employee->update(['fingerprint_id' => $request->fingerprint_id]);

        return response()->json(['message' => 'Fingerprint registered successfully']);
    }

    public function recordAttendance(Request $request)
    {
        $employee = Employee::where('fingerprint_id', $request->fingerprint_id)->first();

        if (!$employee) {
            return response()->json(['message' => 'Fingerprint not recognized'], 404);
        }

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereNull('time_out')
            ->first();

        if ($attendance) {
            $attendance->update(['time_out' => now()]);
            return response()->json(['message' => 'Time Out Recorded']);
        } else {
            Attendance::create([
                'employee_id' => $employee->id,
                'time_in' => now(),
            ]);
            return response()->json(['message' => 'Time In Recorded']);
        }
    }
}
