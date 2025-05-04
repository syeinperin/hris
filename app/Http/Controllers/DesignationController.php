<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    /**
     * Display a listing of designations,
     * optionally filtered by ?search=
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $term   = $search ? "%{$search}%" : null;

        $designations = Designation::latest()
            ->when($term, function($q) use ($term) {
                $q->where('name','like',$term);
            })
            ->paginate(10)
            ->withQueryString();

        return view('designations.index', compact('designations'));
    }

    public function create()
    {
        return view('designations.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|unique:designations,name',
            'rate_per_hour' => 'nullable|numeric'
        ]);
        Designation::create($request->all());
        return redirect()->route('designations.index')
                         ->with('success', 'Designation added successfully.');
    }

    public function show(Designation $designation)
    {
        return view('designations.show', compact('designation'));
    }

    public function edit(Designation $designation)
    {
        return view('designations.edit', compact('designation'));
    }

    public function update(Request $request, Designation $designation)
    {
        $request->validate([
            'name'             => 'required|unique:designations,name,'.$designation->id,
            'rate_per_hour'  => 'nullable|numeric'
        ]);
        $designation->update($request->all());
        return redirect()->route('designations.index')
                         ->with('success', 'Designation updated successfully.');
    }

    public function destroy(Designation $designation)
    {
        $designation->delete();
        return redirect()->route('designations.index')
                         ->with('success', 'Designation deleted successfully.');
    }
}
