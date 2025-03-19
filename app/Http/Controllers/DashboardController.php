<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Loan;
use App\Models\Sidebar; // Ensure Item model is imported

class DashboardController extends Controller
{
    public function index() 
    {
        $employeeCount = Employee::count();
        $loanCount = Loan::count();
        $items = Sidebar::all(); // Fetch data from the database

        return view('dashboard', compact('employeeCount', 'loanCount', 'items'));
    }    
}
