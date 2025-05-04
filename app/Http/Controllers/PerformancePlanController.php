<?php

namespace App\Http\Controllers;

use App\Models\PerformancePlan;
use Illuminate\Http\Request;

class PerformancePlanController extends Controller
{
    public function index()
    {
        $plans = PerformancePlan::orderBy('name')->paginate(15);
        return view('plans.index', compact('plans'));
    }

    public function create()
    {
        return view('plans.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|unique:performance_plans,name',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            // no 'notes' here
        ]);

        // create the plan (only name + dates)
        $plan = PerformancePlan::create([
            'name'       => $data['name'],
            'start_date' => $data['start_date'] ?? null,
            'end_date'   => $data['end_date']   ?? null,
        ]);

        // if you also want to save KPI items:
        /*
        foreach ($request->input('items', []) as $item) {
            $plan->items()->create([
                'metric' => $item['metric'],
                'weight' => $item['weight'],
            ]);
        }
        */

        return redirect()
            ->route('plans.index')
            ->with('success', 'Plan created.');
    }

    public function edit(PerformancePlan $plan)
    {
        return view('plans.edit', compact('plan'));
    }

    public function update(Request $request, PerformancePlan $plan)
    {
        $data = $request->validate([
            'name'       => 'required|string|unique:performance_plans,name,'.$plan->id,
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        $plan->update([
            'name'       => $data['name'],
            'start_date' => $data['start_date'] ?? null,
            'end_date'   => $data['end_date']   ?? null,
        ]);

        return redirect()
            ->route('plans.index')
            ->with('success', 'Plan updated.');
    }

    public function destroy(PerformancePlan $plan)
    {
        $plan->delete();
        return back()->with('success', 'Plan deleted.');
    }
}
