<?php

namespace App\Http\Controllers;

use App\Models\Deduction;
use App\Models\Employee;
use Illuminate\Http\Request;

class DeductionController extends Controller
{
    /**
     * Display a listing of deductions.
     */
    public function index(Request $request)
    {
        $query = Deduction::with('employee')->latest();

        if ($term = $request->input('search')) {
            $query->where('description', 'like', "%{$term}%")
                  ->orWhereHas('employee', fn($q) =>
                      $q->where('name', 'like', "%{$term}%")
                        ->orWhere('employee_code', 'like', "%{$term}%")
                  );
        }

        $deductions = $query->paginate(10)->withQueryString();
        $employees  = Employee::orderBy('name')->pluck('name','id')->toArray();

        return view('deductions.index', compact('deductions','employees'));
    }

    /**
     * Store new deduction(s).
     * If employee_id is "all", create one record per employee.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id'      => 'required|string',
            'description'      => 'required|string|max:255',
            'amount'           => 'required|numeric|min:0',
            'effective_from'   => 'required|date',
            'effective_until'  => 'nullable|date|after_or_equal:effective_from',
            'notes'            => 'nullable|string',
        ]);

        if ($data['employee_id'] === 'all') {
            $allIds = Employee::pluck('id');
            foreach ($allIds as $id) {
                Deduction::create([
                    'employee_id'     => $id,
                    'description'     => $data['description'],
                    'amount'          => $data['amount'],
                    'effective_from'  => $data['effective_from'],
                    'effective_until' => $data['effective_until'],
                    'notes'           => $data['notes'] ?? null,
                ]);
            }
        } else {
            Deduction::create([
                'employee_id'     => $data['employee_id'],
                'description'     => $data['description'],
                'amount'          => $data['amount'],
                'effective_from'  => $data['effective_from'],
                'effective_until' => $data['effective_until'],
                'notes'           => $data['notes'] ?? null,
            ]);
        }

        return back()->with('success','Deduction saved.');
    }

    /**
     * Show the form for editing a deduction.
     */
    public function edit(Deduction $deduction)
    {
        $employees = Employee::orderBy('name')->pluck('name','id')->toArray();
        return view('deductions.edit', compact('deduction','employees'));
    }

    /**
     * Update a single deduction record.
     */
    public function update(Request $request, Deduction $deduction)
    {
        $data = $request->validate([
            'employee_id'      => 'required|exists:employees,id',
            'description'      => 'required|string|max:255',
            'amount'           => 'required|numeric|min:0',
            'effective_from'   => 'required|date',
            'effective_until'  => 'nullable|date|after_or_equal:effective_from',
            'notes'            => 'nullable|string',
        ]);

        $deduction->update($data);

        return redirect()->route('deductions.index')
                         ->with('success','Deduction updated.');
    }

    /**
     * Remove a deduction record.
     */
    public function destroy(Deduction $deduction)
    {
        $deduction->delete();
        return back()->with('success','Deduction removed.');
    }
}
