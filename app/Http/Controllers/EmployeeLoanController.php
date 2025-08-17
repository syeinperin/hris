<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;

class EmployeeLoanController extends Controller
{
    /**
     * Only authenticated users may access these methods.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the logged-in employee’s loans.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Grab the related employee record
        $employee = auth()->user()->employee;

        // Fetch that employee’s loans, newest first, paginated
        $loans = Loan::where('employee_id', $employee->id)
                     ->orderByDesc('created_at')
                     ->paginate(10);

        // Render the new blade: resources/views/loans/employee_index.blade.php
        return view('loans.employee_index', compact('loans'));
    }
}
