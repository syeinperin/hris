<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\User;      // <-- Import User model
use App\Models\Sidebar;

class DashboardController extends Controller
{
    public function index()
    {
        $employeeCount = Employee::count();
        // Count how many users are pending approval
        $pendingUsersCount  = User::where('status', 'pending')->count();

        // If you need sidebar items or any other data
        $items = Sidebar::all();

        // Pass $employeeCount and $pendingCount to the view
        return view('dashboard', compact('employeeCount', 'pendingUsersCount', 'items'));
    }
}
