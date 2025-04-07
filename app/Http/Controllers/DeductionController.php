<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deduction;
use Illuminate\Support\Facades\Log;

class DeductionController extends Controller
{
    // Display a list of deduction settings
    public function index()
    {
        $deductions = Deduction::all();
        return view('deductions.index', compact('deductions'));
    }

    // Show the form for editing a deduction
    public function edit($id)
    {
        $deduction = Deduction::findOrFail($id);
        return view('deductions.edit', compact('deduction'));
    }

    // Update a deduction
    public function update(Request $request, $id)
    {
        $deduction = Deduction::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'nullable|numeric|min:0',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string',
        ]);

        $deduction->update($data);
        return redirect()->route('deductions.index')->with('success', 'Deduction updated successfully!');
    }
}
