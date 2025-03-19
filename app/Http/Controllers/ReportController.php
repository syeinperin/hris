<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index'); // Make sure 'reports.index' exists in resources/views/reports/
    }
}

