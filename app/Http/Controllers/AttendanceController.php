<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    // List all attendances and return a Blade view.
    public function index()
    {
        $attendances = Attendance::with('user')->latest()->get();
        return view('attendance.index', compact('attendances'));
    }

    // Record time in for the authenticated user.
    public function store(Request $request)
    {
        Attendance::create([
            'user_id' => Auth::id(),
            'time_in' => now(),
        ]);

        return redirect()->back()->with('success', 'Time In recorded.');
    }

    // Record time out for a specific attendance record.
    public function timeout($id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->update(['time_out' => now()]);

        return redirect()->back()->with('success', 'Time Out recorded.');
    }
}
