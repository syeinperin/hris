<?php

namespace App\Http\Controllers\Discipline;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Discipline\InfractionReport;
use App\Models\Discipline\InfractionInvestigator;
use App\Models\Employee;
use App\Models\User;

class InfractionReportController extends Controller
{
    public function index()
    {
        $reports = InfractionReport::with('employee','reporter')->paginate(10);
        return view('discipline::infractions.index', compact('reports'));
    }

    public function create()
    {
        return view('discipline::infractions.create', [
            'employees'=>Employee::all(),
            'users'=>User::all(),
        ]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'employee_id'=>'required|exists:employees,id',
            'reported_by'=>'required|exists:users,id',
            'location'=>'required|string',
            'description'=>'required|string',
            'incident_date'=>'required|date',
            'incident_time'=>'nullable',
            'similar_before'=>'boolean',
            'similar_count'=>'nullable|integer',
            'confidential'=>'boolean',
            'will_testify'=>'boolean',
        ]);

        $inf = InfractionReport::create($data);

        foreach ($r->input('investigators',[]) as $uid) {
            InfractionInvestigator::create([
                'infraction_report_id'=>$inf->id,
                'user_id'=>$uid
            ]);
        }

        return redirect()->route('discipline.infractions.index')
                         ->with('success','Report logged.');
    }

    public function show(InfractionReport $infraction)
    {
        $infraction->load('investigators.investigator','actions.type');
        return view('discipline::infractions.show', compact('infraction'));
    }
}
