<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FingerPrintController extends Controller
{
    public function __invoke(Request $request)
    {
        $now = now();

        $validated = $request->validate([
            'employee_code' => 'required|string',
            'fingerprint_template' => 'required|string',
            'type' => 'required|in:0,1',
        ]);

        $employee = Employee::where('employee_code', $validated['employee_code'])->first();
        if (!$employee) {
            return response()->json(['message' => 'Employee not found.'], 404);
        }

        if (is_null($employee->fingerprint_id)) {
            $extract = Http::post('http://localhost:5260/api/fingerprint/extract-template', [
                'ImageBase64' => $validated['fingerprint_template'],
            ]);

            if ($extract->failed()) {
                return response()->json(['message' => 'Template extraction failed'], 500);
            }

            $template = $extract->json()['template'] ?? null;
            if (!$template) {
                return response()->json(['message' => 'Template extraction returned empty template'], 500);
            }

            $employee->update(['fingerprint_id' => $template]);

            return response()->json(['message' => 'Fingerprint registered successfully.'], 200);
        }

        $match = Http::post('http://localhost:5260/api/fingerprint/match', [
            'ProbeImageBase64' => $validated['fingerprint_template'],
            'CandidateTemplateBase64' => $employee->fingerprint_id,
        ]);

        if ($match->failed()) {
            return response()->json(['message' => 'Fingerprint match request failed'], 500);
        }

        $matchResult = $match->json();
        if (empty($matchResult['match'])) {
            return response()->json(['message' => 'Fingerprint mismatch.'], 403);
        }

        $today = $now->toDateString();
        $type = (int) $validated['type'];

        $result = DB::transaction(function () use ($employee, $today, $type, $now, $validated) {
            $attendance = Attendance::where('employee_id', $employee->id)
                ->whereDate('created_at', $today)
                ->lockForUpdate()
                ->first();

            if ($type === 0) {
                // TIME IN
                if ($attendance) {
                    if ($attendance->time_in) {
                        return ['status' => 409, 'message' => 'Already clocked in today.'];
                    }
                    $attendance->time_in = $now;
                    $attendance->save();
                } else {
                    $attendance = Attendance::create([
                        'employee_id' => $employee->id,
                        'schedule_id' => null,
                        'time_in' => $now,
                        'time_out' => null,
                        'is_manual' => false,
                    ]);
                }

                return ['status' => 200, 'message' => 'Time in recorded.', 'attendance' => $attendance];
            }

            // TIME OUT
            if (!$attendance) {
                return ['status' => 409, 'message' => 'No time-in found for today.'];
            }

            if (!$attendance->time_in) {
                return ['status' => 409, 'message' => 'Cannot time out before time in.'];
            }

            if ($attendance->time_out) {
                return ['status' => 409, 'message' => 'Already clocked out today.'];
            }

            $attendance->time_out = $now;
            $attendance->save();

            return ['status' => 200, 'message' => 'Time out recorded.', 'attendance' => $attendance];
        });

        return response()->json(
            ['message' => $result['message'], 'attendance' => $result['attendance'] ?? null],
            $result['status']
        );
    }
}
