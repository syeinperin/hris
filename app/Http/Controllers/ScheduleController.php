<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the schedules.
     */
    public function index()
    {
        $schedules = Schedule::latest()->get();
        return view('attendance.schedule', compact('schedules'));
    }

    /**
     * Store a newly created schedule in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|regex:/^[\w-]+$/|unique:schedules,name',
            'time_in'  => 'required|date_format:H:i',
            'time_out' => 'required|date_format:H:i|after:time_in',
        ]);

        Schedule::create($request->only('name', 'time_in', 'time_out'));

        return redirect()->route('schedule.index')->with('success', 'Schedule created successfully!');
    }
}
