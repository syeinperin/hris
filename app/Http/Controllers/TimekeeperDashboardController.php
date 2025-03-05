<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TimekeeperDashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.timekeeper');
    }
}
