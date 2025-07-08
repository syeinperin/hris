<?php

namespace App\Http\Controllers;

use App\Models\LateDeduction;
use Illuminate\Http\Request;

class LateDeductionController extends Controller
{
    public function index()
    {
        $brackets = LateDeduction::orderBy('mins_min')->get();
        return view('late_deductions.index', compact('brackets'));
    }

    public function create()
    {
        return view('late_deductions.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'mins_min'   => 'required|integer|min:1',
            'mins_max'   => 'required|integer|gt:mins_min',
            'multiplier' => 'required|numeric|min:0',
        ]);

        LateDeduction::create($data);

        return redirect()->route('late_deductions.index')
                         ->with('success','Bracket added.');
    }

    public function edit(LateDeduction $lateDeduction)
    {
        return view('late_deductions.edit', compact('lateDeduction'));
    }

    public function update(Request $request, LateDeduction $lateDeduction)
    {
        $data = $request->validate([
            'mins_min'   => 'required|integer|min:1',
            'mins_max'   => 'required|integer|gt:mins_min',
            'multiplier' => 'required|numeric|min:0',
        ]);

        $lateDeduction->update($data);

        return redirect()->route('late_deductions.index')
                         ->with('success','Bracket updated.');
    }

    public function destroy(LateDeduction $lateDeduction)
    {
        $lateDeduction->delete();

        return back()->with('success','Bracket removed.');
    }
}
