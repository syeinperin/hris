<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    public function index(Request $request)
    {
        $query = Designation::query();

        // Search Functionality
        if ($request->has('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $designations = $query->paginate(10);
        return view('designations.index', compact('designations'));
    }

    public function create()
    {
        return view('designations.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|unique:designations,name',
            'rate_per_hour' => 'nullable|numeric'
        ]);

        // Using mass assignment; ensure 'rate_per_hour' is in your $fillable in Designation model
        Designation::create($request->all());

        return redirect()->route('designations.index')->with('success', 'Designation added successfully.');
    }

    public function edit(Designation $designation)
    {
        return view('designations.edit', compact('designation'));
    }

    public function update(Request $request, Designation $designation)
    {
        $request->validate([
            'name'          => 'required|unique:designations,name,' . $designation->id,
            'rate_per_hour' => 'nullable|numeric'
        ]);

        $designation->update($request->all());

        return redirect()->route('designations.index')->with('success', 'Designation updated successfully.');
    }

    public function destroy(Designation $designation)
    {
        $designation->delete();

        return redirect()->route('designations.index')->with('success', 'Designation deleted successfully.');
    }
}
