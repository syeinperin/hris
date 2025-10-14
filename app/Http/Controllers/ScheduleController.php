<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Schedule;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    /**
     * List schedules and show bulk-assign UI.
     */
    public function index(Request $request)
    {
        // existing schedules list
        $schedules = Schedule::orderBy('time_in')->paginate(10);

        // NEW: filters for employee list
        $deptId = $request->query('dept_id');
        $q      = trim($request->query('q', ''));

        $departments = Department::orderBy('name')->get(['id','name']);

        $empQuery = Employee::query()
            ->whereIn('status', ['active','pending'])
            ->when($deptId, fn($q2)=> $q2->where('department_id', $deptId))
            ->when($q, function ($q2) use ($q) {
                $q2->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('employee_code', 'like', "%{$q}%");
                });
            })
            ->select('id','employee_code','name','department_id','schedule_id')
            ->with(['department:id,name','schedule:id,name']);

        // keep pages light
        $employees = $empQuery->orderBy('name')->paginate(15)->withQueryString();

        return view('attendance.schedule', compact('schedules','departments','employees','deptId','q'));
    }

    /**
     * Create a schedule.
     * NOTE: allow overnight (22:00 -> 06:00).
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
     * Update a schedule.
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

    /**
     * NEW: Bulk-assign a schedule to many employees.
     * Uses current employees.schedule_id (no history).
     */
    public function assignStore(Request $request)
    {
        $data = $request->validate([
            'schedule_id'   => ['required', 'exists:schedules,id'],
            'employee_ids'  => ['required', 'array', 'min:1'],
            'employee_ids.*'=> ['integer', 'exists:employees,id'],
            'mode'          => ['nullable', Rule::in(['replace'])], // future-proof
        ]);

        DB::transaction(function () use ($data) {
            Employee::whereIn('id', $data['employee_ids'])
                ->update(['schedule_id' => $data['schedule_id']]);
        });

        return redirect()->route('schedule.index')
            ->with('success', 'Schedule assigned to selected employees.');
    }
}
