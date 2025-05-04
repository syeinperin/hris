<?php

namespace App\Http\Controllers;

use App\Models\PerformanceEvaluation;
use App\Models\PerformancePlan;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;

class PerformanceEvaluationController extends Controller
{
    public function index(Request $request)
    {
        $employees = Employee::orderBy('name')->pluck('name','id');
        $users     = User::orderBy('name')->pluck('name','id');
        $plans     = PerformancePlan::orderBy('name')->pluck('name','id');  // ← name, not title

        $evals = PerformanceEvaluation::with(['employee','evaluator','plan'])
            ->when($request->employee_id,  fn($q) => $q->where('employee_id',           $request->employee_id))
            ->when($request->evaluator_id, fn($q) => $q->where('evaluator_id',          $request->evaluator_id))
            ->when($request->plan_id,      fn($q) => $q->where('performance_plan_id',   $request->plan_id))
            ->when($request->status,       fn($q) => $q->where('status',                $request->status))
            ->orderBy('evaluation_date','desc')
            ->paginate(15)
            ->withQueryString();

        return view('evaluation.index', compact('evals','employees','users','plans'));
    }

    public function create()
    {
        $employees = Employee::orderBy('name')->pluck('name','id');
        $users     = User::orderBy('name')->pluck('name','id');
        $plans     = PerformancePlan::orderBy('name')->pluck('name','id');

        return view('evaluation.create', compact('employees','users','plans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id'         => 'required|exists:employees,id',
            'evaluator_id'        => 'required|exists:users,id',
            'performance_plan_id' => 'required|exists:performance_plans,id',
            'evaluation_date'     => 'required|date',
            'status'              => 'required|in:pending,completed',
            'comments'            => 'nullable|string',
        ]);

        PerformanceEvaluation::create($data);

        return redirect()
            ->route('evaluation.index')
            ->with('success','Evaluation added.');
    }

    public function edit(PerformanceEvaluation $evaluation)
    {
        $employees = Employee::orderBy('name')->pluck('name','id');
        $users     = User::orderBy('name')->pluck('name','id');
        $plans     = PerformancePlan::orderBy('name')->pluck('name','id');

        return view('evaluation.edit', compact('evaluation','employees','users','plans'));
    }

    public function update(Request $request, PerformanceEvaluation $evaluation)
    {
        $data = $request->validate([
            'employee_id'         => 'required|exists:employees,id',
            'evaluator_id'        => 'required|exists:users,id',
            'performance_plan_id' => 'required|exists:performance_plans,id',
            'evaluation_date'     => 'required|date',
            'status'              => 'required|in:pending,completed',
            'comments'            => 'nullable|string',
        ]);

        $evaluation->update($data);

        return redirect()
            ->route('evaluation.index')
            ->with('success','Evaluation updated.');
    }

    public function destroy(PerformanceEvaluation $evaluation)
    {
        $evaluation->delete();
        return back()->with('success','Evaluation removed.');
    }
}
