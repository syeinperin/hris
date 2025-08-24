<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\PerformanceEvaluation;
use App\Models\DisciplinaryAction;
use Carbon\Carbon;

class EmployeeEvaluationController extends Controller
{
    public function index()
    {
        $employee = Employee::where('user_id', auth()->id())->firstOrFail();

        $evaluations = PerformanceEvaluation::with(['evaluator'])
            ->where('employee_id', $employee->id)
            ->orderByDesc('period_end')
            ->paginate(10);

        // Attach discipline summary per evaluation row
        $evaluations->getCollection()->transform(function ($e) use ($employee) {
            $e->discipline_summary = $this->summarizeDiscipline(
                $employee->id,
                $e->period_start,
                $e->period_end
            );
            return $e;
        });

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

        // Add discipline summary to the paged list
        $evaluations->getCollection()->transform(function ($e) use ($employee) {
            $e->discipline_summary = $this->summarizeDiscipline(
                $employee->id,
                $e->period_start,
                $e->period_end
            );
            return $e;
        });

        // Detailed actions for the selected evaluation (to show in modal)
        [$periodActions, $disciplineSummary] = $this->actionsForPeriod(
            $employee->id,
            $evaluation->period_start,
            $evaluation->period_end
        );

        return view('evaluations.employee_index', [
            'employee'          => $employee,
            'evaluations'       => $evaluations,
            'showEval'          => $evaluation, // <-- triggers the modal
            'periodActions'     => $periodActions,
            'disciplineSummary' => $disciplineSummary,
        ]);
    }

    /**
     * Build a compact summary for index rows.
     */
    private function summarizeDiscipline(int $employeeId, $from, $to): array
    {
        [$actions, $summary] = $this->actionsForPeriod($employeeId, $from, $to);
        return $summary;
    }

    /**
     * Fetch all violations & suspensions that fall within [from, to],
     * and compute a summary (counts + total suspension days overlapped).
     */
    private function actionsForPeriod(int $employeeId, $from, $to): array
    {
        $from = Carbon::parse($from)->startOfDay();
        $to   = Carbon::parse($to)->endOfDay();

        $actions = DisciplinaryAction::where('employee_id', $employeeId)
            ->where(function ($q) use ($from, $to) {
                // Suspensions overlapping the period
                $q->where(function ($qq) use ($from, $to) {
                    $qq->where('action_type', 'suspension')
                       ->whereDate('start_date', '<=', $to->toDateString())
                       ->whereDate('end_date',   '>=', $from->toDateString());
                })
                // OR point-in-time violations whose created_at is inside the period
                ->orWhere(function ($qq) use ($from, $to) {
                    $qq->where('action_type', 'violation')
                       ->whereDate('created_at', '>=', $from->toDateString())
                       ->whereDate('created_at', '<=', $to->toDateString());
                });
            })
            ->orderByDesc('created_at')
            ->get();

        $violationsCount  = $actions->where('action_type','violation')->count();
        $suspensions      = $actions->where('action_type','suspension');
        $suspensionsCount = $suspensions->count();

        $suspensionDays = $suspensions->sum(function ($a) use ($from, $to) {
            return $a->suspensionDaysInRange($from, $to);
        });

        $summary = [
            'violations'       => $violationsCount,
            'suspensions'      => $suspensionsCount,
            'suspension_days'  => $suspensionDays,
        ];

        return [$actions, $summary];
    }
}
