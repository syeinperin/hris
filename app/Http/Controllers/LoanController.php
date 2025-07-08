<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Employee;
use App\Models\LoanType;
use App\Models\LoanPlan;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    protected function baseData(Request $request)
    {
        // same query you had in index()
        $loans = Loan::with(['employee','loanType','plan'])
            ->when($request->search, fn($q,$s) =>
                $q->where('reference_no','like',"%{$s}%")
                  ->orWhereHas('employee', fn($q2) =>
                      $q2->where('name','like',"%{$s}%")
                  )
            )
            ->paginate(10);

        $employees = Employee::pluck('name','id');
        $types     = LoanType::pluck('name','id');
        $plans     = LoanPlan::pluck('name','id');

        return compact('loans','employees','types','plans');
    }

    public function index(Request $request)
    {
        // no editLoan => just show index
        $data = $this->baseData($request);
        return view('loans.index', $data);
    }

    public function store(Request $request)
    {
        Loan::create($request->validate([
            'employee_id'       => 'required|exists:employees,id',
            'loan_type_id'      => 'required|exists:loan_types,id',
            'plan_id'           => 'required|exists:loan_plans,id',
            'principal'         => 'required|numeric',
            'interest_rate'     => 'required|numeric',
            'term_months'       => 'required|integer',
            'next_payment_date' => 'required|date',
            'released_at'       => 'required|date',
        ]));

        return back()->with('success','Loan created');
    }

    public function edit(Request $request, Loan $loan)
    {
        // build exactly the same index data...
        $data = $this->baseData($request);

        // but also pass an "editLoan" so index.blade can show the modal
        $data['editLoan'] = $loan;

        return view('loans.index', $data);
    }

    public function update(Request $request, Loan $loan)
    {
        $loan->update($request->validate([
            'employee_id'       => 'required|exists:employees,id',
            'loan_type_id'      => 'required|exists:loan_types,id',
            'plan_id'           => 'required|exists:loan_plans,id',
            'principal'         => 'required|numeric',
            'interest_rate'     => 'required|numeric',
            'term_months'       => 'required|integer',
            'next_payment_date' => 'required|date',
            'released_at'       => 'required|date',
        ]));

        return back()->with('success','Loan updated');
    }

    public function destroy(Loan $loan)
    {
        $loan->delete();
        return back()->with('success','Loan removed');
    }
}
