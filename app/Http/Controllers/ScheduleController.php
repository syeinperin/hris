<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;

class ScheduleController extends Controller
{
    /**
     * Display a listing of schedules.
     */
    public function index()
    {
        $schedules = Schedule::all();
        return view('attendance.schedule', compact('schedules'));
    }

    /**
     * Store a newly created schedule in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name'     => 'required|string|unique:schedules,name',
            'time_in'  => 'required',
            'time_out' => 'required',
        ]);

        Schedule::create($validatedData);

        return redirect()->route('schedule.index')
                         ->with('success', 'Schedule created successfully.');
    }

    /**
     * Show the form for editing the specified schedule.
     */
    public function edit($id)
    {
        $schedule = Schedule::findOrFail($id);
        return view('attendance.schedule_edit', compact('schedule'));
    }

    /**
     * Update the specified schedule in storage.
     */
    public function update(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);

        $validatedData = $request->validate([
            'name'     => 'required|string|unique:schedules,name,'.$schedule->id,
            'time_in'  => 'required',
            'time_out' => 'required',
        ]);

        $schedule->update($validatedData);

        return redirect()->route('schedule.index')
                         ->with('success', 'Schedule updated successfully.');
    }

    /**
     * Remove the specified schedule from storage.
     */
    public function destroy($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();

        return redirect()->route('schedule.index')
                         ->with('success', 'Schedule deleted successfully.');
    }
}
