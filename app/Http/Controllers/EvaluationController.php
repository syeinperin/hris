<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\PerformanceFormAssignment;
use App\Models\PerformanceEvaluation;
use App\Models\PerformanceEvaluationDetail;

class EvaluationController extends Controller
{
    /**
     * Show both “Fill” and “Completed” lists (the view will toggle based on route).
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Fetch all assignments for this evaluator
        $all = PerformanceFormAssignment::with('form','employee.user')
            ->where('evaluator_id', $user->id)
            ->get();

        // Split into pending vs completed
        $pendingCollection = $all->reject(function($a) use($user) {
            return PerformanceEvaluation::where([
                'form_id'      => $a->form_id,
                'employee_id'  => $a->employee_id,
                'evaluator_id' => $user->id,
            ])->exists();
        })->values();

        $completedCollection = PerformanceEvaluation::with('form','employee.user')
            ->where('evaluator_id', $user->id)
            ->orderByDesc('evaluated_on')
            ->get();

        // Paginate both
        $perPage = 10;
        $pendingPage   = $request->input('pending_page',   1);
        $completedPage = $request->input('completed_page', 1);

        $pending = new LengthAwarePaginator(
            $pendingCollection->forPage($pendingPage, $perPage),
            $pendingCollection->count(),
            $perPage, $pendingPage,
            ['path'=>route('evaluations.index'), 'pageName'=>'pending_page']
        );

        $completed = new LengthAwarePaginator(
            $completedCollection->forPage($completedPage, $perPage),
            $completedCollection->count(),
            $perPage, $completedPage,
            ['path'=>route('evaluations.index'), 'pageName'=>'completed_page']
        );

        return view('evaluations.index', compact('pending','completed'));
    }

    /**
     * Alias for index() when the “Completed” tab is clicked.
     */
    public function completed(Request $request)
    {
        return $this->index($request);
    }

    /**
     * Show the fill‐out form for one assignment.
     */
    public function show($formId, $employeeId)
    {
        $user = auth()->user();

        PerformanceFormAssignment::where([
            'form_id'      => $formId,
            'employee_id'  => $employeeId,
            'evaluator_id' => $user->id,
        ])->firstOrFail();

        $assignment = PerformanceFormAssignment::with('form','employee')
            ->where('form_id',$formId)
            ->where('employee_id',$employeeId)
            ->first();

        $criteria = $assignment->form->criteria;

        return view('evaluations.show', [
            'form'     => $assignment->form,
            'employee' => $assignment->employee,
            'criteria' => $criteria,
        ]);
    }

    /**
     * Persist a submitted evaluation.
     */
    public function store(Request $request, $formId, $employeeId)
    {
        $user = auth()->user();

        PerformanceFormAssignment::where([
            'form_id'      => $formId,
            'employee_id'  => $employeeId,
            'evaluator_id' => $user->id,
        ])->firstOrFail();

        $data = $request->validate([
            'ratings'      => 'required|array',
            'ratings.*'    => 'required|string|in:' . implode(',', array_keys(PerformanceEvaluationDetail::ratingOptions())),
            'remarks'      => 'nullable|array',
            'remarks.*'    => 'nullable|string',
            'comments'     => 'nullable|string',
        ]);

        $evaluation = PerformanceEvaluation::create([
            'form_id'      => $formId,
            'employee_id'  => $employeeId,
            'evaluator_id' => $user->id,
            'evaluated_on' => now(),
            'comments'     => $data['comments'] ?? null,
            'total_score'  => 0,
        ]);

        $total = 0;
        foreach($data['ratings'] as $critId => $rating){
            $detail = $evaluation->details()->create([
                'criterion_id'=> $critId,
                'rating'      => $rating,
                'comments'    => $data['remarks'][$critId] ?? null,
            ]);
            $total += $detail->weighted_score;
        }

        $evaluation->update(['total_score' => $total]);

        return redirect()
            ->route('evaluations.index')
            ->with('success','Evaluation submitted.');
    }
}
