<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class InactiveUserController extends Controller
{
    public function index() {
        $inactiveUsers = Employee::where('status', 'inactive')->get();
        return view('inactive_users.index', compact('inactiveUsers'));
    }
}

