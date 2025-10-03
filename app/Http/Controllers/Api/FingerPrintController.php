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

            'type' => 'required|numeric',
        ]);
        Log::info($request->fingerprint_template);
        $employee = Employee::where('employee_code', $validated['employee_code'])->first();
        if (!$employee) {
            return response()->json(['message' => 'Employee not found.'], 404);
        }

        // Registration phase (no template yet in DB)
        if (is_null($employee->fingerprint_template)) {
            $response = Http::post('http://localhost:5260/api/fingerprint/extract-template', [
                'ImageBase64' => $validated['fingerprint_template']
            ]);

            if ($response->failed()) {
                return response()->json(['message' => 'Template extraction failed'], 500);
            }

            $template = $response->json()['template'];

            $employee->update([
                'fingerprint_template' => $template,
            ]);

            return response()->json(['message' => 'Fingerprint registered successfully.'], 200);
        }

        $response = Http::post('http://localhost:5260/api/fingerprint/match', [
            'ProbeImageBase64' => $validated['fingerprint_template'],
            'CandidateTemplateBase64' => $employee->fingerprint_template,
        ]);

        Log::info($response);

        if ($response->failed()) {
            return response()->json(['message' => 'Fingerprint match request failed'], 500);
        }

        $matchResult = $response->json();

        if (!$matchResult['match']) {
            return response()->json(['message' => 'Fingerprint mismatch.'], 403);
        }

        $employee->attendanceHistories()->create([
            'scanned_at' => $now,
            'type' => $validated['type'],
        ]);

        return response()->json(['message' => 'Attendance recorded.'], 200);
    }
}
