<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        $attendances = Attendance::with('user')->latest()->get();
        return view('attendance.index', compact('attendances'));
    }

    public function store(Request $request)
{
    Attendance::create([
        'user_id' => Auth::id(),
        'time_in' => now(),
    ]);

    return redirect()->back()->with('success', 'Time In recorded.');
}

    public function timeout($id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->update(['time_out' => now()]);

        return redirect()->back()->with('success', 'Time Out recorded.');
    }

}
