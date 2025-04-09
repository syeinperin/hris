<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    /**
     * Display the payroll report using per-minute rates.
     *
     * It calculates total worked time (in seconds) from attendance logs, 
     * converts them to minutes, and computes gross pay using rate_per_minute.
     *
     * IMPORTANT: Verify that your kiosk attendance records store full date+time 
     * in the time_in and time_out fields. If not, consider using whereDate on time_in 
     * or filtering by created_at.
     */
    public function index(Request $request)
    {
        // Retrieve date inputs. If your UI sends dates as dd/mm/yyyy, adjust accordingly.
        $startDateInput = $request->input('start_date');
        $endDateInput   = $request->input('end_date');

        if ($startDateInput) {
            try {
                // Try parsing assuming dd/mm/yyyy format.
                $start_date = Carbon::createFromFormat('d/m/Y', $startDateInput)->startOfDay();
            } catch (\Exception $e) {
                // Fallback: use the built-in parsing.
                $start_date = Carbon::parse($startDateInput)->startOfDay();
            }
        } else {
            $start_date = Carbon::now()->startOfMonth();
        }

        if ($endDateInput) {
            try {
                $end_date = Carbon::createFromFormat('d/m/Y', $endDateInput)->endOfDay();
            } catch (\Exception $e) {
                $end_date = Carbon::parse($endDateInput)->endOfDay();
            }
        } else {
            $end_date = Carbon::now()->endOfMonth();
        }

        // Option 2: Using whereDate filtering in attendances.
        $employees = Employee::with([
            'designation',
            'attendances' => function ($query) use ($start_date, $end_date) {
                $query->whereDate('time_in', '>=', $start_date->toDateString())
                      ->whereDate('time_in', '<=', $end_date->toDateString());
            }
        ])->get();

        // Process each employee's attendances.
        foreach ($employees as $employee) {
            // (a) Get per-minute rate from the designation.
            $designation   = $employee->designation;
            $ratePerMinute = $designation ? $designation->rate_per_minute : 0;

            // (b) Initialize a counter for total seconds worked.
            $totalSeconds = 0;

            // (c) Loop through each attendance record.
            foreach ($employee->attendances as $attendance) {
                if ($attendance->time_in && $attendance->time_out) {
                    $timeIn  = Carbon::parse($attendance->time_in);
                    $timeOut = Carbon::parse($attendance->time_out);

                    // If time_out is before time_in, adjust for shifts crossing midnight.
                    if ($timeOut->lessThan($timeIn)) {
                        $timeOut->setDate($timeIn->year, $timeIn->month, $timeIn->day);
                        if ($timeOut->lessThan($timeIn)) {
                            $timeOut->addDay();
                        }
                    }

                    // Calculate the difference in seconds.
                    $diff = $timeOut->diffInSeconds($timeIn, false);
                    if ($diff > 0) {
                        $totalSeconds += $diff;
                    }
                }
            }

            // (d) Convert seconds to minutes.
            $totalMinutes = $totalSeconds / 60;

            // (e) Calculate gross pay.
            $grossPay = $ratePerMinute * $totalMinutes;

            // (f) Set deductions and cash advance.
            $deduction      = 0;
            $cashAdvance    = 0;
            $totalDeduction = $deduction + $cashAdvance;
            $netPay         = $grossPay - $totalDeduction;

            // (g) Attach calculated values to the employee object for use in the view.
            $employee->rate_per_minute = $ratePerMinute;
            $employee->total_minutes   = $totalMinutes;
            $employee->gross_pay       = $grossPay;
            $employee->deduction       = $deduction;
            $employee->cash_advance    = $cashAdvance;
            $employee->total_deduction = $totalDeduction;
            $employee->net_pay         = $netPay;
        }

        return view('payroll.index', compact('employees'))->with([
            'start_date' => $start_date->toDateString(),
            'end_date'   => $end_date->toDateString()
        ]);
    }

    /**
     * Generate and display a payslip for a specific employee.
     */
    public function show($id, Request $request)
    {
        $employee = Employee::with(['designation', 'attendances'])->findOrFail($id);

        $startDateInput = $request->input('start_date');
        $endDateInput   = $request->input('end_date');

        if ($startDateInput) {
            try {
                $start_date = Carbon::createFromFormat('d/m/Y', $startDateInput)->startOfDay();
            } catch (\Exception $e) {
                $start_date = Carbon::parse($startDateInput)->startOfDay();
            }
        } else {
            $start_date = Carbon::now()->startOfMonth();
        }

        if ($endDateInput) {
            try {
                $end_date = Carbon::createFromFormat('d/m/Y', $endDateInput)->endOfDay();
            } catch (\Exception $e) {
                $end_date = Carbon::parse($endDateInput)->endOfDay();
            }
        } else {
            $end_date = Carbon::now()->endOfMonth();
        }

        // Filter the employee's attendances within the payroll period.
        $filteredAttendances = $employee->attendances->filter(function ($attendance) use ($start_date, $end_date) {
            return Carbon::parse($attendance->time_in)->between($start_date, $end_date);
        });

        // Compute total worked seconds.
        $totalSeconds = 0;
        foreach ($filteredAttendances as $attendance) {
            if ($attendance->time_in && $attendance->time_out) {
                $timeIn  = Carbon::parse($attendance->time_in);
                $timeOut = Carbon::parse($attendance->time_out);

                if ($timeOut->lessThan($timeIn)) {
                    $timeOut->setDate($timeIn->year, $timeIn->month, $timeIn->day);
                    if ($timeOut->lessThan($timeIn)) {
                        $timeOut->addDay();
                    }
                }

                $diff = $timeOut->diffInSeconds($timeIn, false);
                if ($diff > 0) {
                    $totalSeconds += $diff;
                }
            }
        }

        $totalMinutes = $totalSeconds / 60;
        $designation  = $employee->designation;
        $ratePerMinute = $designation ? $designation->rate_per_minute : 0;
        $grossPay     = $ratePerMinute * $totalMinutes;

        $deduction      = 0;
        $cashAdvance    = 0;
        $totalDeduction = $deduction + $cashAdvance;
        $netPay         = $grossPay - $totalDeduction;

        $employee->rate_per_minute = $ratePerMinute;
        $employee->total_minutes   = $totalMinutes;
        $employee->gross_pay       = $grossPay;
        $employee->deduction       = $deduction;
        $employee->cash_advance    = $cashAdvance;
        $employee->total_deduction = $totalDeduction;
        $employee->net_pay         = $netPay;

        return view('payroll.show', compact('employee'))->with([
            'start_date' => $start_date->toDateString(),
            'end_date'   => $end_date->toDateString()
        ]);
    }
}
