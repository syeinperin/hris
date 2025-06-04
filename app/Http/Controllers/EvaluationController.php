<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PerformanceForm;
use App\Models\Employee;
use App\Models\PerformanceEvaluation;
use App\Models\PerformanceFormAssignment;
use Symfony\Component\HttpFoundation\Response;

class EvaluationController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // List only the forms & employees assigned to this evaluator
        $assignments = PerformanceFormAssignment::with('form','employee.user')
            ->where('evaluator_id', $user->id)
            ->get();

        return view('evaluations.index', compact('assignments'));
    }

    public function show(PerformanceForm $form, Employee $employee)
    {
        $user = auth()->user();

        // Guard: ensure this evaluator is actually assigned
        $assigned = PerformanceFormAssignment::where([
            'form_id'      => $form->id,
            'employee_id'  => $employee->id,
            'evaluator_id' => $user->id,
        ])->exists();

        if (! $assigned) {
            abort(Response::HTTP_FORBIDDEN, 'You are not authorized to evaluate this employee.');
        }

        $criteria = $form->criteria;

        return view('evaluations.show', compact('form','employee','criteria'));
    }

    public function store(Request $request, PerformanceForm $form, Employee $employee)
    {
        $user = auth()->user();

        // Guard: ensure assignment still valid
        $assigned = PerformanceFormAssignment::where([
            'form_id'      => $form->id,
            'employee_id'  => $employee->id,
            'evaluator_id' => $user->id,
        ])->exists();

        if (! $assigned) {
            abort(Response::HTTP_FORBIDDEN, 'You are not authorized to submit this evaluation.');
        }

        // Validate scores + comments
        $data = $request->validate([
            'scores'   => 'required|array',
            'scores.*' => 'required|integer|min:0',
            'comments' => 'nullable|string',
        ]);

        // Create evaluation header
        $evaluation = PerformanceEvaluation::create([
            'form_id'      => $form->id,
            'employee_id'  => $employee->id,
            'evaluator_id' => $user->id,
            'evaluated_on' => now(),
            'comments'     => $data['comments'] ?? null,
            'total_score'  => 0,
        ]);

        // Save details & compute total
        $total = 0;
        foreach ($data['scores'] as $criterionId => $score) {
            $evaluation->details()->create([
                'criterion_id'   => $criterionId,
                'evaluated_score'=> $score,
            ]);
            $total += $score;
        }

        // Update with total
        $evaluation->update(['total_score' => $total]);

        return redirect()
            ->route('evaluations.index')
            ->with('success','Evaluation submitted.');
    }
}
