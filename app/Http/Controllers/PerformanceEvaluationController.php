<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\PerformanceItem;
use App\Models\PerformanceEvaluation;
use App\Models\PerformanceScore;

class PerformanceEvaluationController extends Controller
{
    protected function listData(Request $request): array
    {
        $q = PerformanceEvaluation::with(['employee','evaluator'])->orderByDesc('period_end');

        if ($s = trim((string) $request->get('search'))) {
            $q->whereHas('employee', fn($qq) => $qq->where('name','like',"%{$s}%"));
        }

        return [
            'evaluations' => $q->paginate(10)->withQueryString(),
            'employees'   => Employee::orderBy('name')->pluck('name','id'),
            'items'       => PerformanceItem::where('is_active',true)->orderBy('display_order')->get(),
        ];
    }

    public function index(Request $request)
    {
        return view('evaluations.index', $this->listData($request));
    }

    public function show(Request $request, PerformanceEvaluation $evaluation)
    {
        $data = $this->listData($request);
        $data['showEval'] = $evaluation->load(['employee','evaluator','scores.item']);
        return view('evaluations.index', $data);
    }

    public function edit(Request $request, PerformanceEvaluation $evaluation)
    {
        $data = $this->listData($request);
        $data['editEval'] = $evaluation->load(['employee','scores.item']);
        return view('evaluations.index', $data);
    }

    public function store(Request $request)
    {
        $items   = PerformanceItem::where('is_active',true)->orderBy('display_order')->get();
        $itemIds = $items->pluck('id')->toArray();

        // Accept multiple employees (employee_ids[]) or a single (employee_id)
        $multi  = is_array($request->input('employee_ids'));

        $rules = [
            'period_start' => 'required|date',
            'period_end'   => 'required|date|after_or_equal:period_start',
            'remarks'      => 'nullable|string',
            'scores'       => 'required|array',
            'scores.*'     => 'required|integer|min:1|max:5',
            'notes'        => 'array',
        ] + (
            $multi
                ? ['employee_ids'   => 'required|array', 'employee_ids.*' => 'exists:employees,id']
                : ['employee_id'    => 'required|exists:employees,id']
        );

        $data = $request->validate($rules);

        foreach ($itemIds as $id) {
            if (! array_key_exists($id, $data['scores'])) {
                return back()->withInput()->withErrors(['scores' => 'Please rate all criteria.']);
            }
        }

        $empIds = $multi ? $data['employee_ids'] : [$data['employee_id']];

        DB::transaction(function () use ($items, $data, $empIds) {
            foreach ($empIds as $empId) {
                $eval = PerformanceEvaluation::create([
                    'employee_id'  => $empId,
                    'evaluator_id' => auth()->id(),
                    'period_start' => $data['period_start'],
                    'period_end'   => $data['period_end'],
                    'remarks'      => $data['remarks'] ?? null,
                    'status'       => 'submitted',
                    'overall_score'=> 0,
                ]);

                $total = 0;
                foreach ($items as $item) {
                    $score  = (int) $data['scores'][$item->id];
                    $weight = (int) $item->weight;
                    $points = ($score / 5) * $weight;

                    PerformanceScore::create([
                        'evaluation_id' => $eval->id,
                        'item_id'       => $item->id,
                        'score'         => $score,
                        'notes'         => $data['notes'][$item->id] ?? null,
                        'weight_cache'  => $weight,
                        'weighted_score'=> $points,
                    ]);

                    $total += $points;
                }

                $eval->update(['overall_score' => round($total, 2)]);
            }
        });

        return back()->with('success', 'Evaluation(s) saved.');
    }

    public function update(Request $request, PerformanceEvaluation $evaluation)
    {
        $items = PerformanceItem::where('is_active',true)->orderBy('display_order')->get();

        $data = $request->validate([
            'period_start'  => 'required|date',
            'period_end'    => 'required|date|after_or_equal:period_start',
            'remarks'       => 'nullable|string',
            'scores'        => 'required|array',
            'scores.*'      => 'required|integer|min:1|max:5',
            'notes'         => 'array',
        ]);

        DB::transaction(function () use ($items, $data, $evaluation) {
            $evaluation->update([
                'period_start' => $data['period_start'],
                'period_end'   => $data['period_end'],
                'remarks'      => $data['remarks'] ?? null,
            ]);

            $total = 0;

            foreach ($items as $item) {
                $score  = (int) $data['scores'][$item->id];
                $weight = (int) $item->weight;
                $points = ($score / 5) * $weight;

                $evaluation->scores()->updateOrCreate(
                    ['item_id' => $item->id],
                    [
                        'score'         => $score,
                        'notes'         => $data['notes'][$item->id] ?? null,
                        'weight_cache'  => $weight,
                        'weighted_score'=> $points,
                    ]
                );

                $total += $points;
            }

            $evaluation->update(['overall_score' => round($total, 2)]);
        });

        return back()->with('success', 'Evaluation updated.');
    }

    public function destroy(PerformanceEvaluation $evaluation)
    {
        $evaluation->delete();
        return back()->with('success','Evaluation removed.');
    }
}
