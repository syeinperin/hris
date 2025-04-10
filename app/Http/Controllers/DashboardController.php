<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Sidebar;

class DashboardController extends Controller
{
    public function index()
    {
        $employeeCount = Employee::count();
        // Comment out or remove loan references
        // $loanCount = Loan::count();

        // Load sidebar items if you need them
        $items = Sidebar::all();

        // Pass only $employeeCount (and $items if needed)
        return view('dashboard', compact('employeeCount', 'items'));
    }
}
