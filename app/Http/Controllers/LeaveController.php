<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveRequest;

class LeaveController extends Controller
{
    /**
     * Map of leave_type keys → labels.
     */
    protected array $types = [
        'sick'      => 'Sick Leave',
        'vacation'  => 'Vacation Leave',
        'casual'    => 'Casual Leave',
        'maternity' => 'Maternity Leave',
        'paternity' => 'Paternity Leave',
    ];

    /**
     * GET  /leaves
     * Show paginated list of the current user's leaves.
     */
    public function index()
    {
        $requests = auth()
            ->user()
            ->leaveRequests()
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('leaves.index', [
            'requests' => $requests,
            'types'    => $this->types,
        ]);
    }

    /**
     * GET  /leaves/create
     * Show the modal form (or separate page) to file a new leave.
     */
    public function create()
    {
        return view('leaves.create', [
            'types' => $this->types,
        ]);
    }

    /**
     * POST /leaves
     * Validate & store a new leave.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'leave_type' => 'required|in:'.implode(',', array_keys($this->types)),
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'nullable|string|max:1000',
        ]);

        $data['user_id'] = auth()->id();
        $data['status']  = 'pending';

        LeaveRequest::create($data);

        return redirect()
            ->route('leaves.index')
            ->with('success', 'Leave request submitted.');
    }

    /**
     * GET  /leaves/{leave}/edit
     * Return JSON so the modal can populate for editing.
     */
    public function edit(LeaveRequest $leave)
    {
        return response()->json([
            'leave' => $leave,
            'types' => $this->types,
        ]);
    }

    /**
     * PUT /leaves/{leave}
     * Validate & update an existing leave.
     */
    public function update(Request $request, LeaveRequest $leave)
    {
        $data = $request->validate([
            'leave_type' => 'required|in:'.implode(',', array_keys($this->types)),
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'nullable|string|max:1000',
        ]);

        $leave->update($data);

        return redirect()
            ->route('leaves.index')
            ->with('success', 'Leave request updated.');
    }

    /**
     * DELETE /leaves/{leave}
     * Remove a leave from the database.
     */
    public function destroy(LeaveRequest $leave)
    {
        $leave->delete();

        return redirect()
            ->route('leaves.index')
            ->with('success', 'Leave request deleted.');
    }
}
