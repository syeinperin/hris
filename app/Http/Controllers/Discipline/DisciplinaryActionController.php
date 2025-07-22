<?php

namespace App\Http\Controllers\Discipline;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Discipline\DisciplinaryAction;
use App\Models\Discipline\InfractionReport;
use Illuminate\Support\Facades\DB;

class DisciplinaryActionController extends Controller
{
    public function index()
    {
        $actions = DisciplinaryAction::with('infraction.employee.user')
                                     ->latest()
                                     ->paginate(10);

        return view('discipline.actions.index', compact('actions'));
    }

    public function create()
    {
        $infractions = InfractionReport::with('employee.user')->get();
        $types       = DB::table('action_types')
                         ->where('active', true)
                         ->orderBy('code')
                         ->get();

        return view('discipline.actions.create', compact('infractions','types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'infraction_report_id' => 'required|exists:infraction_reports,id',
            'action_type_id'       => 'required|exists:action_types,id',
            'action_date'          => 'required|date',
        ]);

        // Build payload ensuring booleans are set
        $data = [
            'infraction_report_id' => $request->infraction_report_id,
            'action_type_id'       => $request->action_type_id,
            'action_date'          => $request->action_date,
            'link_payroll'         => $request->has('link_payroll'),
            'link_hiring'          => $request->has('link_hiring'),
            'terminate_employee'   => $request->has('terminate_employee'),
        ];

        DisciplinaryAction::create($data);

        return redirect()
            ->route('discipline.actions.index')
            ->with('success','Disciplinary action created.');
    }

    public function edit(DisciplinaryAction $action)
    {
        $infractions = InfractionReport::with('employee.user')->get();
        $types       = DB::table('action_types')
                         ->where('active', true)
                         ->orderBy('code')
                         ->get();

        return view('discipline.actions.edit', compact('action','infractions','types'));
    }

    public function update(Request $request, DisciplinaryAction $action)
    {
        $request->validate([
            'infraction_report_id' => 'required|exists:infraction_reports,id',
            'action_type_id'       => 'required|exists:action_types,id',
            'action_date'          => 'required|date',
        ]);

        $action->update([
            'infraction_report_id' => $request->infraction_report_id,
            'action_type_id'       => $request->action_type_id,
            'action_date'          => $request->action_date,
            'link_payroll'         => $request->has('link_payroll'),
            'link_hiring'          => $request->has('link_hiring'),
            'terminate_employee'   => $request->has('terminate_employee'),
        ]);

        return redirect()
            ->route('discipline.actions.index')
            ->with('success','Disciplinary action updated.');
    }

    public function destroy(DisciplinaryAction $action)
    {
        $action->delete();

        return redirect()
            ->route('discipline.actions.index')
            ->with('success','Disciplinary action removed.');
    }
}
