<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Payslip;
use App\Models\Attendance;
use App\Models\Deduction;
use Carbon\Carbon;

class PayslipController extends Controller
{
    public function index()
    {
        $payslips = Auth::user()
                        ->payslips()
                        ->orderByDesc('created_at')
                        ->paginate(10);

        return view('payslips.index', compact('payslips'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'period_start' => 'required|date',
            'period_end'   => 'required|date|after_or_equal:period_start',
        ]);

        // 1) Get the employee_id
        $employee = Auth::user()->employee;
        if (! $employee) {
            return back()->with('error','Complete your employee profile first.');
        }
        $empId = $employee->id;

        // 2) Total minutes worked in period
        $minutes = Attendance::where('employee_id', $empId)
            ->whereBetween('time_in', [
                Carbon::parse($data['period_start'])->startOfDay(),
                Carbon::parse($data['period_end'])->endOfDay(),
            ])
            ->whereNotNull('time_out')
            ->get()
            ->sum(fn($a) => Carbon::parse($a->time_in)->diffInMinutes($a->time_out));

        $workedHours = round($minutes / 60, 2);

        // 3) Overtime minutes and hours
        $otMinutes = Attendance::where('employee_id', $empId)
            ->whereBetween('time_in', [
                Carbon::parse($data['period_start'])->startOfDay(),
                Carbon::parse($data['period_end'])->endOfDay(),
            ])
            ->whereNotNull('time_out')
            ->get()
            ->sum(fn($a) => max(0, Carbon::parse($a->time_in)->diffInHours($a->time_out) - 8) * 60);

        $otHours = round($otMinutes / 60, 2);

        // 4) Base rate and OT pay
        $baseRate = optional($employee->designation)->rate_per_hour ?: 0;
        $otPay    = round($otHours * $baseRate * 1.25, 2);

        // 5) Deductions
        $deductions = Deduction::where('employee_id', $empId)
            ->whereBetween('created_at', [
                Carbon::parse($data['period_start'])->startOfDay(),
                Carbon::parse($data['period_end'])->endOfDay(),
            ])->sum('amount');

        // 6) Gross & Net
        $gross = round($workedHours * $baseRate + $otPay, 2);
        $net   = round($gross - $deductions, 2);

        Payslip::create([
            'user_id'      => Auth::id(),
            'period_start' => $data['period_start'],
            'period_end'   => $data['period_end'],
            'worked_hours' => $workedHours,
            'ot_hours'     => $otHours,
            'ot_pay'       => $otPay,
            'deductions'   => $deductions,
            'gross_amount' => $gross,
            'net_amount'   => $net,
        ]);

        return back()->with('success','Payslip generated.');
    }

    public function download(Payslip $payslip)
    {
        // Ensure the payslip belongs to this user
        abort_unless($payslip->user_id === Auth::id(), 403);

        $employee = Auth::user()->employee;
        $empId    = $employee->id;

        // Re-compute worked + OT + deductions (same logic as above)
        $minutes = Attendance::where('employee_id', $empId)
            ->whereBetween('time_in', [
                $payslip->period_start->startOfDay(),
                $payslip->period_end->endOfDay(),
            ])
            ->whereNotNull('time_out')
            ->get()
            ->sum(fn($a) => Carbon::parse($a->time_in)->diffInMinutes($a->time_out));
        $workedHours = round($minutes/60,2);

        $otMinutes = Attendance::where('employee_id', $empId)
            ->whereBetween('time_in', [
                $payslip->period_start->startOfDay(),
                $payslip->period_end->endOfDay(),
            ])
            ->whereNotNull('time_out')
            ->get()
            ->sum(fn($a) => max(0, Carbon::parse($a->time_in)->diffInHours($a->time_out) - 8) * 60);
        $otHours = round($otMinutes/60,2);

        $baseRate   = optional($employee->designation)->rate_per_hour ?: 0;
        $otPay      = round($otHours * $baseRate * 1.25, 2);
        $deductions = Deduction::where('employee_id', $empId)
            ->whereBetween('created_at', [
                $payslip->period_start->startOfDay(),
                $payslip->period_end->endOfDay(),
            ])->sum('amount');

        $pdf = Pdf::loadView('payslips.pdf', [
            'payslip'      => $payslip,
            'worked_hours' => $workedHours,
            'ot_hours'     => $otHours,
            'ot_pay'       => $otPay,
            'deductions'   => $deductions,
            'gross'        => $payslip->gross_amount,
            'net'          => $payslip->net_amount,
        ]);

        return $pdf->download("payslip-{$payslip->id}.pdf");
    }
}
