<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    /**
     * Display the payroll report with summary details.
     */
    public function index(Request $request)
    {
        // Set the payroll period (default: current month)
        $start_date = $request->input('start_date') ?? Carbon::now()->startOfMonth()->toDateString();
        $end_date   = $request->input('end_date') ?? Carbon::now()->endOfMonth()->toDateString();

        // Eager-load necessary relationships (e.g., designation)
        $employees = Employee::with('designation')->get();

        foreach ($employees as $employee) {
            // 1. Rate per Hour: from the employee's designation
            $ratePerHour = $employee->designation->rate_per_hour ?? 0;

            // 2. Total Hours: Replace with actual logic (e.g., sum from an Attendance table)
            $totalHours = 160; // Example: 160 working hours; replace as needed

            // 3. Gross Pay: Rate per Hour multiplied by Total Hours
            $grossPay = $ratePerHour * $totalHours;

            // 4. Deduction: Replace with your own logic; currently, no deductions by default
            $deduction = 0; // Replace with actual deduction calculation if available

            // 5. Cash Advance: Replace with your own logic; currently, no cash advance by default
            $cashAdvance = 0; // Replace with actual cash advance calculation if available

            // 6. Total Deduction: Sum of deduction and cash advance
            $totalDeduction = $deduction + $cashAdvance;

            // 7. Net Pay: Gross Pay minus Total Deduction
            $netPay = $grossPay - $totalDeduction;

            // Attach calculated fields to the employee object
            $employee->rate_per_hour   = $ratePerHour;
            $employee->total_hours     = $totalHours;
            $employee->gross_pay       = $grossPay;
            $employee->deduction       = $deduction;
            $employee->cash_advance    = $cashAdvance;
            $employee->total_deduction = $totalDeduction;
            $employee->net_pay         = $netPay;
        }

        return view('payroll.index', compact('employees', 'start_date', 'end_date'));
    }

    /**
     * Generate and display a payslip for a specific employee.
     */
    public function show($id)
    {
        $employee = Employee::with('designation')->findOrFail($id);

        $ratePerHour = $employee->designation->rate_per_hour ?? 0;
        $totalHours  = 160; // Replace with actual logic as needed
        $grossPay    = $ratePerHour * $totalHours;

        // Replace with your own logic for deductions and cash advances
        $deduction   = 0;
        $cashAdvance = 0;

        $totalDeduction = $deduction + $cashAdvance;
        $netPay = $grossPay - $totalDeduction;

        // Attach calculated fields to the employee object
        $employee->rate_per_hour   = $ratePerHour;
        $employee->total_hours     = $totalHours;
        $employee->gross_pay       = $grossPay;
        $employee->deduction       = $deduction;
        $employee->cash_advance    = $cashAdvance;
        $employee->total_deduction = $totalDeduction;
        $employee->net_pay         = $netPay;

        return view('payroll.show', compact('employee'));
    }
}
