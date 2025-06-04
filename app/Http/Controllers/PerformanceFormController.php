<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PerformanceForm;
use App\Models\PerformanceFormAssignment;

class PerformanceFormController extends Controller
{
    public function index()
    {
        $forms = PerformanceForm::withCount(['criteria','assignments'])->paginate(10);
        return view('performance_forms.index', compact('forms'));
    }

    public function create()
    {
        return view('performance_forms.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'         => 'required|string',
            'description'   => 'nullable|string',
            'evaluator_id'  => 'required|exists:users,id',
            'employee_ids'  => 'required|array|min:1',
            'employee_ids.*'=> 'exists:employees,id',
            'criteria'      => 'required|array|min:1',
            'criteria.*.text'          => 'required|string',
            'criteria.*.default_score' => 'required|integer|min:0',
        ]);

        // 1) Create the form
        $form = PerformanceForm::create([
            'title'        => $data['title'],
            'description'  => $data['description'] ?? '',
            'created_by'   => auth()->id(),
            'evaluator_id' => $data['evaluator_id'],
        ]);

        // 2) Criteria
        foreach ($data['criteria'] as $c) {
            $form->criteria()->create($c);
        }

        // 3) Assign employees
        foreach ($data['employee_ids'] as $eid) {
            PerformanceFormAssignment::create([
                'form_id'      => $form->id,
                'employee_id'  => $eid,
                'evaluator_id' => $data['evaluator_id'],
            ]);
        }

        return redirect()
            ->route('performance.forms.index')
            ->with('success','Form created.');
    }

    public function edit(PerformanceForm $form)
    {
        $form->load(['criteria','assignments']);
        return view('performance_forms.edit', compact('form'));
    }

    public function update(Request $request, PerformanceForm $form)
    {
        $data = $request->validate([
            'title'         => 'required|string',
            'description'   => 'nullable|string',
            'evaluator_id'  => 'required|exists:users,id',
            'employee_ids'  => 'required|array|min:1',
            'employee_ids.*'=> 'exists:employees,id',
            'criteria'      => 'required|array|min:1',
            'criteria.*.id'             => 'nullable|exists:performance_criteria,id',
            'criteria.*.text'           => 'required|string',
            'criteria.*.default_score'  => 'required|integer|min:0',
        ]);

        // update form header
        $form->update([
            'title'        => $data['title'],
            'description'  => $data['description'] ?? '',
            'evaluator_id' => $data['evaluator_id'],
        ]);

        // sync criteria: delete & recreate for simplicity
        $form->criteria()->delete();
        foreach ($data['criteria'] as $c) {
            $form->criteria()->create([
                'text'          => $c['text'],
                'default_score' => $c['default_score'],
            ]);
        }

        // sync assignments
        $form->assignments()->delete();
        foreach ($data['employee_ids'] as $eid) {
            PerformanceFormAssignment::create([
                'form_id'      => $form->id,
                'employee_id'  => $eid,
                'evaluator_id' => $data['evaluator_id'],
            ]);
        }

        return redirect()
            ->route('performance.forms.index')
            ->with('success','Form updated.');
    }

    public function destroy(PerformanceForm $form)
    {
        $form->delete();
        return back()->with('success','Form deleted.');
    }
}
