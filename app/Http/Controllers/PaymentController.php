<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Payment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function store(Request $request, Loan $loan)
    {
        $data = $request->validate([
            'payment_date' => 'required|date',
            'amount'       => 'required|numeric|min:0',
            'penalty'      => 'nullable|numeric|min:0',
        ]);

        Payment::create([
            'loan_id'      => $loan->id,
            'payment_date' => $data['payment_date'],
            'amount'       => $data['amount'],
            'penalty'      => $data['penalty'] ?? 0,
        ]);

        // advance next date
        $loan->next_payment_date = Carbon::parse($loan->next_payment_date)->addMonth();
        if ($loan->payments()->count() >= $loan->term_months) {
            $loan->status = 'paid';
        }
        $loan->save();

        return back()->with('success','Payment recorded.');
    }
}
