<?php

namespace App\Http\Controllers;

use App\Models\Disciplinary;
use App\Models\Employee;
use Illuminate\Http\Request;

class DisciplinaryController extends Controller
{
    public function index() {
        $disciplinary = Disciplinary::with('employee')->get();
        return view('disciplinary.index', compact('disciplinary'));
    }

    public function create() {
        $employees = Employee::all();
        return view('disciplinary.create', compact('employees'));
    }

    public function store(Request $request) {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'title' => 'required',
            'description' => 'required',
            'status' => 'required',
        ]);

        Disciplinary::create($request->all());
        return redirect()->route('disciplinary.index')->with('success', 'Disciplinary action added successfully.');
    }
}

