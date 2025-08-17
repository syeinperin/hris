<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\PerformanceEvaluation;

class EmployeeEvaluationController extends Controller
{
    public function index()
    {
        $employee = Employee::where('user_id', auth()->id())->firstOrFail();

        $evaluations = PerformanceEvaluation::with(['evaluator'])
            ->where('employee_id', $employee->id)
            ->orderByDesc('period_end')
            ->paginate(10);

        return view('evaluations.employee_index', compact('employee','evaluations'));
    }

    // Return the INDEX view with a $showEval variable so it opens a modal
    public function show(PerformanceEvaluation $evaluation)
    {
        $employee = Employee::where('user_id', auth()->id())->firstOrFail();
        abort_unless($evaluation->employee_id === $employee->id, 403);

        $evaluation->load(['evaluator','scores.item','employee']);

        // Rebuild the list so we can render the table + modal on the same page
        $evaluations = PerformanceEvaluation::with(['evaluator'])
            ->where('employee_id', $employee->id)
            ->orderByDesc('period_end')
            ->paginate(10);

        return view('evaluations.employee_index', [
            'employee'    => $employee,
            'evaluations' => $evaluations,
            'showEval'    => $evaluation, // <-- triggers the modal
        ]);
    }
}
