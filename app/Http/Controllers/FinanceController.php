<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FinanceController extends Controller
{
    /**
     * Display the payroll dashboard.
     */
    public function payroll()
    {
        return view('finance.payroll');
    }

    /**
     * Generate payslips.
     */
    public function generatePayslip()
    {
        return view('finance.payslip_generate');
    }

    /**
     * Display the payslip report.
     */
    public function payslipReport()
    {
        return view('finance.payslip_report');
    }

    /**
     * Manage loans.
     */
    public function loans()
    {
        return view('finance.loans');
    }
}
