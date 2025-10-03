<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanType;
use App\Models\LoanPlan;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));

        $loans = Loan::with(['employee','loanType','plan'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where('reference_no', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($qq) use ($search) {
                      $qq->where('name', 'like', "%{$search}%")
                         ->orWhere('employee_code', 'like', "%{$search}%");
                  });
            })
            ->latest('released_at')
            ->paginate(10);

        // for the modal form
        $employees = Employee::orderBy('name')->pluck('name','id');
        $types     = LoanType::orderBy('name')->pluck('name','id');
        $plans     = LoanPlan::orderBy('months')->pluck('name','id');

        return view('loans.index', compact('loans','employees','types','plans'));
    }

    public function store(Request $request)
    {
        $rules = [
            'employee_id'        => 'required|exists:employees,id',
            'loan_type_id'       => 'required|exists:loan_types,id',
            'plan_id'            => 'required|exists:loan_plans,id',
            'principal_amount'   => 'nullable|numeric|min:0.01',
            'principal'          => 'nullable|numeric|min:0.01',
            'interest_rate'      => 'nullable|numeric|min:0',
            'term_months'        => 'nullable|integer|min:1',
            'next_payment_date'  => 'required|date',
            'released_at'        => 'required|date',
            'status'             => 'nullable|in:active,paid,defaulted',
        ];
        $data = $request->validate($rules);

        $plan = LoanPlan::findOrFail($data['plan_id']);

        $principal = (float)($data['principal_amount'] ?? $data['principal'] ?? 0);
        if ($principal <= 0) {
            return back()->withInput()->with('error', 'Principal amount is required.');
        }

        $rate   = isset($data['interest_rate']) ? (float)$data['interest_rate'] : (float)$plan->interest_rate;
        $months = isset($data['term_months'])   ? (int)$data['term_months']   : (int)$plan->months;

        $total   = round($principal * (1 + ($rate / 100)), 2);
        $monthly = round($total / max(1, $months), 2);

        Loan::create([
            'employee_id'       => $data['employee_id'],
            // reference_no auto-generates in the Loan model
            'loan_type_id'      => $data['loan_type_id'],
            'plan_id'           => $data['plan_id'],
            'principal_amount'  => $principal,
            'interest_rate'     => $rate,
            'term_months'       => $months,
            'total_payable'     => $total,
            'monthly_amount'    => $monthly,
            'next_payment_date' => $data['next_payment_date'],
            'status'            => $data['status'] ?? 'active',
            'released_at'       => $data['released_at'],
        ]);

        return redirect()->route('loans.index')->with('success', 'Loan created.');
    }

    public function edit(Loan $loan, Request $request)
    {
        $search = trim($request->input('search', ''));

        $loans = Loan::with(['employee','loanType','plan'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where('reference_no', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($qq) use ($search) {
                      $qq->where('name', 'like', "%{$search}%")
                         ->orWhere('employee_code', 'like', "%{$search}%");
                  });
            })
            ->latest('released_at')
            ->paginate(10);

        $employees = Employee::orderBy('name')->pluck('name','id');
        $types     = LoanType::orderBy('name')->pluck('name','id');
        $plans     = LoanPlan::orderBy('months')->pluck('name','id');

        $editLoan = $loan; // to open the edit modal on the same page
        return view('loans.index', compact('loans','employees','types','plans','editLoan'));
    }

    public function update(Request $request, Loan $loan)
    {
        $rules = [
            'employee_id'        => 'required|exists:employees,id',
            'loan_type_id'       => 'required|exists:loan_types,id',
            'plan_id'            => 'required|exists:loan_plans,id',
            'principal_amount'   => 'nullable|numeric|min:0.01',
            'principal'          => 'nullable|numeric|min:0.01',
            'interest_rate'      => 'nullable|numeric|min:0',
            'term_months'        => 'nullable|integer|min:1',
            'next_payment_date'  => 'required|date',
            'released_at'        => 'required|date',
            'status'             => 'nullable|in:active,paid,defaulted',
        ];
        $data = $request->validate($rules);

        $plan = LoanPlan::findOrFail($data['plan_id']);

        $principal = (float)($data['principal_amount'] ?? $data['principal'] ?? $loan->principal_amount);
        $rate      = isset($data['interest_rate']) ? (float)$data['interest_rate'] : (float)$plan->interest_rate;
        $months    = isset($data['term_months'])   ? (int)$data['term_months']   : (int)$plan->months;

        $total   = round($principal * (1 + ($rate / 100)), 2);
        $monthly = round($total / max(1, $months), 2);

        $loan->update([
            'employee_id'       => $data['employee_id'],
            'loan_type_id'      => $data['loan_type_id'],
            'plan_id'           => $data['plan_id'],
            'principal_amount'  => $principal,
            'interest_rate'     => $rate,
            'term_months'       => $months,
            'total_payable'     => $total,
            'monthly_amount'    => $monthly,
            'next_payment_date' => $data['next_payment_date'],
            'status'            => $data['status'] ?? $loan->status,
            'released_at'       => $data['released_at'],
        ]);

        return redirect()->route('loans.index')->with('success', 'Loan updated.');
    }

    public function destroy(Loan $loan)
    {
        $loan->delete();
        return back()->with('success', 'Loan removed.');
    }

    // Keep resource completeness; we render everything on index
    public function create() { return redirect()->route('loans.index'); }
    public function show(Loan $loan) { return redirect()->route('loans.edit', $loan); }

    // ─────────────── Employee self-service page ───────────────
    public function myLoans(Request $request)
    {
        $userId = Auth::id();

        // Safely resolve employee_id for this login (relation may not exist)
        $employeeId = Employee::where('user_id', $userId)->value('id');

        if (!$employeeId) {
            // Return a real paginator that's simply empty (no Collection::paginate issue)
            $loans = Loan::whereRaw('1=0')->paginate(10);

            return view('loans.employee_index', compact('loans'))
                ->with('error', 'No employee profile is linked to your account.');
        }

        $loans = Loan::with(['loanType','plan'])
            ->where('employee_id', $employeeId)
            ->when($request->filled('status'), function($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->orderByDesc('released_at')
            ->paginate(10);

        // Your actual view path: resources/views/loans/employee_index.blade.php
        return view('loans.employee_index', compact('loans'));
    }
}
