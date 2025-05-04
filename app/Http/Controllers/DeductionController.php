<?php

namespace App\Http\Controllers;

use App\Models\Deduction;
use App\Models\Employee;
use Illuminate\Http\Request;

class DeductionController extends Controller
{
    public function index(Request $request)
    {
        $query = Deduction::with('employee')->latest();

        if ($term = $request->input('search')) {
            $query->where('description', 'like', "%{$term}%")
                  ->orWhereHas('employee', fn($q) => 
                       $q->where('name', 'like', "%{$term}%"));
        }

        $deductions = $query->paginate(10)->withQueryString();
        $employees  = Employee::orderBy('name')->pluck('name','id');

        return view('deductions.index', compact('deductions','employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employees'        => 'required|array|min:1',
            'employees.*'      => 'exists:employees,id',
            'description'      => 'required|string|max:255',
            'amount'           => 'required|numeric|min:0',
            'effective_from'   => 'required|date',
            'effective_until'  => 'required|date|after_or_equal:effective_from',
            'notes'            => 'nullable|string',
        ]);

        foreach ($data['employees'] as $empId) {
            Deduction::create([
                'employee_id'     => $empId,
                'description'     => $data['description'],
                'amount'          => $data['amount'],
                'effective_from'  => $data['effective_from'],
                'effective_until' => $data['effective_until'],
                'notes'           => $data['notes'] ?? null,
            ]);
        }

        return back()->with('success','Deduction(s) saved.');
    }

    public function edit(Deduction $deduction)
    {
        $employees = Employee::orderBy('name')->pluck('name','id');
        return view('deductions.edit', compact('deduction','employees'));
    }

    public function update(Request $request, Deduction $deduction)
    {
        $data = $request->validate([
            'employee_id'      => 'required|exists:employees,id',
            'description'      => 'required|string|max:255',
            'amount'           => 'required|numeric|min:0',
            'effective_from'   => 'required|date',
            'effective_until'  => 'required|date|after_or_equal:effective_from',
            'notes'            => 'nullable|string',
        ]);

        $deduction->update($data);

        return redirect()->route('deductions.index')
                         ->with('success','Deduction updated.');
    }

    public function destroy(Deduction $deduction)
    {
        $deduction->delete();
        return back()->with('success','Deduction removed.');
    }
}
