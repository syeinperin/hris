<?php

namespace App\Http\Controllers\Discipline;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Discipline\InfractionReport;
use App\Models\Discipline\InfractionInvestigator;
use App\Models\Employee;
use App\Models\User;
use App\Notifications\DisciplinaryActionCreated;

class InfractionReportController extends Controller
{
    /**
     * Display a listing of the infraction reports.
     */
    public function index()
    {
        $reports   = InfractionReport::with('employee.user','reporter')
                     ->latest()
                     ->paginate(10);

        $employees = Employee::with('user')->get();
        $users     = User::all();

        return view('discipline.infractions.index', compact(
            'reports','employees','users'
        ));
    }

    /**
     * Show the form for creating a new report.
     */
    public function create()
    {
        $employees = Employee::with('user')->get();
        $users     = User::all();

        return view('discipline.infractions.create', compact('employees','users'));
    }

    /**
     * Store a newly created infraction report and notify the employee.
     */
    public function store(Request $r)
    {
        $data = $r->validate([
            'employee_id'    => 'required|exists:employees,id',
            'reported_by'    => 'required|exists:users,id',
            'location'       => 'required|string',
            'description'    => 'required|string',
            'incident_date'  => 'required|date',
            'incident_time'  => 'nullable',
            'similar_before' => 'boolean',
            'similar_count'  => 'nullable|integer',
            'confidential'   => 'boolean',
            'will_testify'   => 'boolean',
        ]);

        // 1) Create the infraction report
        $inf = InfractionReport::create($data);

        // 2) Attach investigators if any
        foreach ($r->input('investigators', []) as $uid) {
            InfractionInvestigator::create([
                'infraction_report_id' => $inf->id,
                'user_id'              => $uid,
            ]);
        }

        // 3) Fire your existing notification
        $employeeUser = $inf->employee->user;
        $employeeUser->notify(new DisciplinaryActionCreated($inf));

        return redirect()
            ->route('discipline.infractions.index')
            ->with('success','Infraction report logged and employee notified.');
    }

    /**
     * Display the specified infraction report.
     */
    public function show(InfractionReport $infraction)
    {
        $infraction->load('investigators.investigator','actions.type');
        return view('discipline.infractions.show', compact('infraction'));
    }

    /**
     * Show the form for editing the specified infraction report.
     */
    public function edit(InfractionReport $infraction)
    {
        $employees = Employee::with('user')->get();
        $users     = User::all();

        return view('discipline.infractions.edit', compact('infraction','employees','users'));
    }

    /**
     * Update the specified infraction report in storage.
     */
    public function update(Request $r, InfractionReport $infraction)
    {
        $data = $r->validate([
            'employee_id'    => 'required|exists:employees,id',
            'reported_by'    => 'required|exists:users,id',
            'location'       => 'required|string',
            'description'    => 'required|string',
            'incident_date'  => 'required|date',
            'incident_time'  => 'nullable',
            'similar_before' => 'boolean',
            'similar_count'  => 'nullable|integer',
            'confidential'   => 'boolean',
            'will_testify'   => 'boolean',
        ]);

        $infraction->update($data);

        // Refresh investigators
        $infraction->investigators()->delete();
        foreach ($r->input('investigators', []) as $uid) {
            InfractionInvestigator::create([
                'infraction_report_id' => $infraction->id,
                'user_id'              => $uid,
            ]);
        }

        return redirect()
            ->route('discipline.infractions.index')
            ->with('success','Infraction report updated.');
    }

    /**
     * Remove the specified infraction report from storage.
     */
    public function destroy(InfractionReport $infraction)
    {
        $infraction->delete();
        return back()->with('success','Infraction report removed.');
    }
}
