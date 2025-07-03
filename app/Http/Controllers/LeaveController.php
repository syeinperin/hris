<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveAllocation;
use Carbon\Carbon;

class LeaveController extends Controller
{
    /**
     * Exactly three leave‐type keys → human labels.
     */
    protected array $types = [
        'service'   => 'Service Incentive Leave',
        'maternity' => 'Maternity Leave',
        'paternity' => 'Paternity Leave',
    ];

    /**
     * GET  /leaves
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->hasAnyRole(['hr','supervisor'])) {
            $requests = LeaveRequest::with(['user','leaveType'])
                            ->latest()
                            ->paginate(15);
        } else {
            $requests = $user->leaveRequests()
                             ->with('leaveType')
                             ->latest()
                             ->paginate(10);
        }

        return view('leaves.index', [
            'requests' => $requests,
            'types'    => $this->types,
        ]);
    }

    /**
     * GET  /leaves/create
     */
    public function create()
    {
        return view('leaves.create', [
            'types' => $this->types,
        ]);
    }

    /**
     * POST /leaves
     */
    public function store(Request $request)
    {
        $valid = implode(',', array_keys($this->types));

        $data = $request->validate([
            'leave_type' => "required|in:{$valid}",
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'nullable|string|max:1000',
        ]);

        $data['user_id'] = auth()->id();
        $data['status']  = 'pending';

        LeaveRequest::create($data);

        return redirect()
            ->route('leaves.index')
            ->with('success','Leave request submitted.');
    }

    /**
     * GET  /leaves/{leave}/edit
     */
    public function edit(LeaveRequest $leave)
    {
        return response()->json([
            'leave' => $leave,
            'types' => $this->types,
        ]);
    }

    /**
     * PUT  /leaves/{leave}
     */
    public function update(Request $request, LeaveRequest $leave)
    {
        $valid = implode(',', array_keys($this->types));

        $data = $request->validate([
            'leave_type' => "required|in:{$valid}",
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'nullable|string|max:1000',
        ]);

        $leave->update($data);

        return redirect()
            ->route('leaves.index')
            ->with('success','Leave request updated.');
    }

    /**
     * DELETE /leaves/{leave}
     */
    public function destroy(LeaveRequest $leave)
    {
        $leave->delete();

        return redirect()
            ->route('leaves.index')
            ->with('success','Leave request deleted.');
    }
}
