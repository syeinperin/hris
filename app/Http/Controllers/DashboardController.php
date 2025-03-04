<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;

class DashboardController extends Controller
{
    public function index()
    {
        $totalEmployees = Employee::count();
        $absentEmployees = Attendance::where('status', 'Absent')->count();

        return view('dashboard', compact('totalEmployees', 'absentEmployees'));
    }
}
