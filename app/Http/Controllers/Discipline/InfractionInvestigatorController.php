<?php

namespace App\Http\Controllers\Discipline;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Discipline\InfractionInvestigator;
use App\Models\Discipline\InfractionReport;
use App\Models\User;

class InfractionInvestigatorController extends Controller
{
    public function index()
    {
        $inv = InfractionInvestigator::with('infraction.employee.user','investigator')->paginate(10);
        return view('discipline::investigators.index', ['investigators'=>$inv]);
    }

    public function create()
    {
        return view('discipline::investigator.create', [
            'reports'=>InfractionReport::all(),
            'users'=>User::all(),
        ]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'infraction_report_id'=>'required|exists:infraction_reports,id',
            'user_id'=>'required|exists:users,id',
        ]);
        InfractionInvestigator::create($data);
        return redirect()->route('discipline.investigators.index')->with('success','Assigned.');
    }

    public function edit(InfractionInvestigator $investigator)
    {
        return view('discipline::investigator.edit', [
            'investigator'=>$investigator,
            'reports'=>InfractionReport::all(),
            'users'=>User::all(),
        ]);
    }

    public function update(Request $r, InfractionInvestigator $investigator)
    {
        $data = $r->validate([
            'infraction_report_id'=>'required|exists:infraction_reports,id',
            'user_id'=>'required|exists:users,id',
        ]);
        $investigator->update($data);
        return redirect()->route('discipline.investigators.index')->with('success','Updated.');
    }

    public function destroy(InfractionInvestigator $investigator)
    {
        $investigator->delete();
        return back()->with('success','Removed.');
    }
}
