<?php

namespace App\Http\Controllers;

use App\Models\LeaveAllocation;
use App\Models\LeaveType;
use App\Models\Employee;
use Illuminate\Http\Request;

class LeaveAllocationController extends Controller
{
    public function index()
    {
        $allocations = LeaveAllocation::with(['leaveType','employee'])
            ->orderBy('year','desc')
            ->paginate(10);
        return view('leaves.allocations.index', compact('allocations'));
    }

    public function create()
    {
        $types     = LeaveType::where('is_active', true)->pluck('name','id');
        $employees = Employee::where('status','active')->pluck('name','id');
        return view('leaves.allocations.create', compact('types','employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'leave_type_id'  => 'required|exists:leave_types,id',
            'employee_id'    => 'required|exists:employees,id',
            'year'           => 'required|integer|min:2000|max:2100',
            'days_allocated' => 'required|integer|min:0',
        ]);
        LeaveAllocation::create($data);
        return redirect()->route('leave-allocations.index')->with('success', 'Leave allocation created.');
    }

    public function show(LeaveAllocation $leaveAllocation)
    {
        return view('leaves.allocations.show', compact('leaveAllocation'));
    }

    public function edit(LeaveAllocation $leaveAllocation)
    {
        $types     = LeaveType::where('is_active', true)->pluck('name','id');
        $employees = Employee::where('status','active')->pluck('name','id');
        return view('leaves.allocations.edit', compact('leaveAllocation','types','employees'));
    }

    public function update(Request $request, LeaveAllocation $leaveAllocation)
    {
        $data = $request->validate([
            'leave_type_id'  => 'required|exists:leave_types,id',
            'employee_id'    => 'required|exists:employees,id',
            'year'           => 'required|integer|min:2000|max:2100',
            'days_allocated' => 'required|integer|min:0',
            'days_used'      => 'required|integer|min:0|max:'.$request->input('days_allocated'),
        ]);
        $leaveAllocation->update($data);
        return redirect()->route('leave-allocations.index')->with('success', 'Leave allocation updated.');
    }

    public function destroy(LeaveAllocation $leaveAllocation)
    {
        $leaveAllocation->delete();
        return redirect()->route('leave-allocations.index')->with('success', 'Leave allocation deleted.');
    }
}
