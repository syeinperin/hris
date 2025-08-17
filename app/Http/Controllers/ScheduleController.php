<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use Illuminate\Validation\Rule;

class ScheduleController extends Controller
{
    /**
     * List schedules (paginated).
     */
    public function index()
    {
        $schedules = Schedule::orderBy('time_in')->paginate(10);
        return view('attendance.schedule', compact('schedules'));
    }

    /**
     * Create a schedule.
     * NOTE: allow overnight (22:00 -> 06:00), so no after_or_equal rule.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'unique:schedules,name'],
            'time_in'  => ['required', 'date_format:H:i'],
            'time_out' => ['required', 'date_format:H:i'],
            'rest_day' => ['nullable', Rule::in([
                'Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'
            ])],
        ]);

        Schedule::create($data);

        return redirect()->route('schedule.index')
            ->with('success', 'Schedule created successfully.');
    }

    /**
     * Update a schedule (uses modal).
     * NOTE: allow overnight (22:00 -> 06:00), so no after_or_equal rule.
     */
    public function update(Request $request, Schedule $schedule)
    {
        $data = $request->validate([
            'name'     => [
                'required','string',
                Rule::unique('schedules','name')->ignore($schedule->id),
            ],
            'time_in'  => ['required', 'date_format:H:i'],
            'time_out' => ['required', 'date_format:H:i'],
            'rest_day' => ['nullable', Rule::in([
                'Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'
            ])],
        ]);

        $schedule->update($data);

        return redirect()->route('schedule.index')
            ->with('success', 'Schedule updated successfully.');
    }

    /**
     * Delete a schedule.
     */
    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return redirect()->route('schedule.index')
            ->with('success', 'Schedule deleted successfully.');
    }

    /**
     * Bulk REST DAY apply to all schedules.
     */
    public function applyRestDayToAll(Request $request)
    {
        $validated = $request->validate([
            'day' => ['required', Rule::in([
                'Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'
            ])],
        ]);

        Schedule::query()->update(['rest_day' => $validated['day']]);

        return redirect()->route('schedule.index')
            ->with('success', "Rest day set to {$validated['day']} for all schedules.");
    }
}
