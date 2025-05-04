<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use Illuminate\Validation\Rule;

class ScheduleController extends Controller
{
    /**
     * Display a paginated listing of schedules.
     */
    public function index()
    {
        // order by start time, 10 per page
        $schedules = Schedule::orderBy('time_in')->paginate(10);

        return view('attendance.schedule', compact('schedules'));
    }

    /**
     * Store a newly created schedule.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required','string','unique:schedules,name'],
            'time_in'  => ['required','date_format:H:i'],
            'time_out' => ['required','date_format:H:i','after:time_in'],
        ]);

        Schedule::create($data);

        return redirect()
            ->route('schedule.index')
            ->with('success', 'Schedule created successfully.');
    }

    /**
     * Update an existing schedule.
     */
    public function update(Request $request, Schedule $schedule)
    {
        $data = $request->validate([
            'name'     => [
                'required','string',
                Rule::unique('schedules','name')->ignore($schedule->id),
            ],
            'time_in'  => ['required','date_format:H:i'],
            'time_out' => ['required','date_format:H:i','after:time_in'],
        ]);

        $schedule->update($data);

        return redirect()
            ->route('schedule.index')
            ->with('success', 'Schedule updated successfully.');
    }

    /**
     * Delete a schedule.
     */
    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return redirect()
            ->route('schedule.index')
            ->with('success', 'Schedule deleted successfully.');
    }
}
