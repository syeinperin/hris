<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    /**
     * Display the payroll report using per-minute rates.
     */
    public function index(Request $request)
    {
        $startDateInput = $request->input('start_date');
        $endDateInput   = $request->input('end_date');

        $start_date = $startDateInput
            ? Carbon::parse($startDateInput)->startOfDay()
            : Carbon::now()->startOfMonth();

        $end_date = $endDateInput
            ? Carbon::parse($endDateInput)->endOfDay()
            : Carbon::now()->endOfMonth();

        $employees = Employee::with([
            'designation',
            'attendances' => function ($query) use ($start_date, $end_date) {
                $query->whereBetween('time_in', [$start_date, $end_date]);
            }
        ])->get();

        foreach ($employees as $employee) {
            $ratePerMinute = $employee->designation->rate_per_minute ?? 0;
            $totalSeconds = 0;

            foreach ($employee->attendances as $attendance) {
                if ($attendance->time_in && $attendance->time_out) {
                    $timeIn  = Carbon::parse($attendance->time_in);
                    $timeOut = Carbon::parse($attendance->time_out);
            
                    if ($timeOut->lt($timeIn)) {
                        $timeOut->addDay(); // Handle overnight shifts
                    }
            
                    $diff = abs($timeOut->diffInSeconds($timeIn));
                    $totalSeconds += $diff;                    
                }
            }            

            $totalMinutes = round($totalSeconds / 60, 2);
            $grossPay = $ratePerMinute * $totalMinutes;

            $deduction = 0;
            $cashAdvance = 0;
            $totalDeduction = $deduction + $cashAdvance;
            $netPay = $grossPay - $totalDeduction;

            $employee->rate_per_minute = $ratePerMinute;
            $employee->total_minutes = $totalMinutes;
            $employee->gross_pay = $grossPay;
            $employee->deduction = $deduction;
            $employee->cash_advance = $cashAdvance;
            $employee->total_deduction = $totalDeduction;
            $employee->net_pay = $netPay;
        }

        return view('payroll.index', compact('employees'))->with([
            'start_date' => $start_date->toDateString(),
            'end_date' => $end_date->toDateString()
        ]);
    }

    /**
     * Generate and display a payslip for a specific employee.
     */
    public function show($id, Request $request)
    {
        $startDateInput = $request->input('start_date');
        $endDateInput   = $request->input('end_date');

        $start_date = $startDateInput
            ? Carbon::parse($startDateInput)->startOfDay()
            : Carbon::now()->startOfMonth();

        $end_date = $endDateInput
            ? Carbon::parse($endDateInput)->endOfDay()
            : Carbon::now()->endOfMonth();

        // ✅ FIXED: assign to $employee
        $employee = Employee::with([
            'designation',
            'attendances' => function ($query) use ($start_date, $end_date) {
                $query->whereBetween('time_in', [$start_date, $end_date]);
            }
        ])->findOrFail($id);

        $ratePerMinute = $employee->designation->rate_per_minute ?? 0;
        $totalSeconds = 0;

        foreach ($employee->attendances as $attendance) {
            if ($attendance->time_in && $attendance->time_out) {
                $timeIn  = Carbon::parse($attendance->time_in);
                $timeOut = Carbon::parse($attendance->time_out);
        
                // Optional: handle overnight shifts
                if ($timeOut->lessThan($timeIn)) {
                    $timeOut->addDay();
                }
        
                // ✅ Always get positive seconds
                $diff = $timeIn->diffInSeconds($timeOut);
                $totalSeconds += $diff;
            }
        }
        

        $totalMinutes = round($totalSeconds / 60, 2);
        $grossPay = $ratePerMinute * $totalMinutes;
        $deduction = 0;
        $cashAdvance = 0;
        $totalDeduction = $deduction + $cashAdvance;
        $netPay = $grossPay - $totalDeduction;

        $employee->rate_per_minute = $ratePerMinute;
        $employee->total_minutes = $totalMinutes;
        $employee->gross_pay = $grossPay;
        $employee->deduction = $deduction;
        $employee->cash_advance = $cashAdvance;
        $employee->total_deduction = $totalDeduction;
        $employee->net_pay = $netPay;

        return view('payroll.show', compact('employee'))->with([
            'start_date' => $start_date->toDateString(),
            'end_date' => $end_date->toDateString()
        ]);
    }
}
