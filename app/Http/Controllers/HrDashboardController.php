<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HrDashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.hr');
    }
}
