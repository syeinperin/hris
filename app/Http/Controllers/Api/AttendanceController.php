<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Log attendance via API.
     *
     * Expects JSON like:
     * {
     *   "user_id": 1,
     *   "timestamp": "2025-03-31 08:00:00",
     *   "type": "check_in" // or "check_out"
     * }
     */
    public function logAttendance(Request $request)
    {
        $validated = $request->validate([
            'user_id'   => 'required|exists:users,id',
            'timestamp' => 'required|date',
            'type'      => 'required|in:check_in,check_out',
        ]);

        if ($validated['type'] === 'check_in') {
            Attendance::create([
                'user_id' => $validated['user_id'],
                'time_in' => $validated['timestamp'],
            ]);
        } else {
            // For check_out, update the most recent attendance record without time_out.
            $attendance = Attendance::where('user_id', $validated['user_id'])
                ->whereNull('time_out')
                ->latest('time_in')
                ->first();

            if ($attendance) {
                $attendance->update([
                    'time_out' => $validated['timestamp'],
                ]);
            }
        }

        return response()->json(['message' => 'Attendance logged successfully'], 200);
    }
}
