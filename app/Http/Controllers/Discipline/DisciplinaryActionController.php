<?php
namespace App\Http\Controllers\Discipline;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Discipline\DisciplinaryAction;
use App\Models\Discipline\InfractionReport;
use App\Models\Discipline\ActionType;

class DisciplinaryActionController extends Controller
{
    public function index()
    {
        // 1) fetch existing actions
        $actions = DisciplinaryAction::with('infraction.employee.user','type')
                                     ->latest()
                                     ->paginate(10);

        // 2) fetch the dropdown lookups for the modal
        $infractions = InfractionReport::with('employee.user')->get();
        $types       = ActionType::where('active', true)->get();

        // 3) pass all three into the view
        return view('discipline.actions.index', compact('actions','infractions','types'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'infraction_report_id' => 'required|exists:infraction_reports,id',
            'action_type_id'       => 'required|exists:action_types,id',
            'action_date'          => 'required|date',
            'link_payroll'         => 'boolean',
            'link_hiring'          => 'boolean',
            'terminate_employee'   => 'boolean',
        ]);

        DisciplinaryAction::create($data);

        return back()->with('success','Disciplinary action created.');
    }

    // …edit(), update(), destroy()…
}
