<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function index()
    {
        $types = LeaveType::orderBy('name')->paginate(10);
        return view('leaves.types.index', compact('types'));
    }

    public function create()
    {
        return view('leaves.types.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255|unique:leave_types,name',
            'default_days' => 'required|integer|min:0',
            'description'  => 'nullable|string',
            'is_active'    => 'sometimes|boolean',
        ]);
        $data['is_active'] = $request->has('is_active');
        LeaveType::create($data);
        return redirect()->route('leave-types.index')->with('success', 'Leave type created.');
    }

    public function show(LeaveType $leaveType)
    {
        return view('leaves.types.show', compact('leaveType'));
    }

    public function edit(LeaveType $leaveType)
    {
        return view('leaves.types.edit', compact('leaveType'));
    }

    public function update(Request $request, LeaveType $leaveType)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255|unique:leave_types,name,'.$leaveType->id,
            'default_days' => 'required|integer|min:0',
            'description'  => 'nullable|string',
            'is_active'    => 'sometimes|boolean',
        ]);
        $data['is_active'] = $request->has('is_active');
        $leaveType->update($data);
        return redirect()->route('leave-types.index')->with('success', 'Leave type updated.');
    }

    public function destroy(LeaveType $leaveType)
    {
        $leaveType->delete();
        return redirect()->route('leave-types.index')->with('success', 'Leave type deleted.');
    }
}
