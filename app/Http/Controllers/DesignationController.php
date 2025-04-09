<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    /**
     * Display a listing of designations.
     */
    public function index(Request $request)
    {
        $query = Designation::query();

        // Search functionality
        if ($request->has('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $designations = $query->paginate(10);

        return view('designations.index', compact('designations'));
    }

    /**
     * Show the form for creating a new designation.
     */
    public function create()
    {
        return view('designations.create');
    }

    /**
     * Store a newly created designation in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'             => 'required|unique:designations,name',
            'rate_per_minute'  => 'nullable|numeric'
        ]);

        Designation::create($request->all());

        return redirect()->route('designations.index')->with('success', 'Designation added successfully.');
    }

    /**
     * Display the specified designation.
     */
    public function show(Designation $designation)
    {
        return view('designations.show', compact('designation'));
    }

    /**
     * Show the form for editing the specified designation.
     */
    public function edit(Designation $designation)
    {
        return view('designations.edit', compact('designation'));
    }

    /**
     * Update the specified designation in storage.
     */
    public function update(Request $request, Designation $designation)
    {
        $request->validate([
            'name'             => 'required|unique:designations,name,' . $designation->id,
            'rate_per_minute'  => 'nullable|numeric'
        ]);

        $designation->update($request->all());

        return redirect()->route('designations.index')->with('success', 'Designation updated successfully.');
    }

    /**
     * Remove the specified designation from storage.
     */
    public function destroy(Designation $designation)
    {
        $designation->delete();

        return redirect()->route('designations.index')->with('success', 'Designation deleted successfully.');
    }
}
