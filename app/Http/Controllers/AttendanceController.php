<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Session;

class AttendanceController extends Controller
{
    // Display attendance list
    public function index()
    {
        return view('attendance.index'); // Ensure this Blade file exists in resources/views/attendance/
    }

    // Show a specific attendance record
    public function show($id)
    {
        $attendance = Attendance::find($id);

        if (!$attendance) {
            return redirect()->route('attendance.index')->with('error', 'Attendance record not found.');
        }

        return view('attendance.show', compact('attendance'));
    }

    // Show import form
    public function importForm()
    {
        return view('attendance.import');
    }

    // Handle attendance import from CSV
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file');

        // Example: Read and process the CSV file
        $handle = fopen($file->getRealPath(), 'r');
        while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
            Attendance::create([
                'employee_id' => $row[0],
                'date' => $row[1],
                'status' => $row[2],
            ]);
        }
        fclose($handle);

        return redirect()->route('attendance.index')->with('success', 'Attendance imported successfully.');
    }

    // Generate attendance report
    public function report()
    {
        $attendances = Attendance::all();
        return view('attendance.report', compact('attendances'));
    }
}
