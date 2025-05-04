<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveRequest;

class LeaveController extends Controller
{
    protected array $types = [
        'sick'      => 'Sick Leave',
        'vacation'  => 'Vacation Leave',
        'casual'    => 'Casual Leave',
        'maternity' => 'Maternity Leave',
        'paternity' => 'Paternity Leave',
    ];

    /** Display a paginated list of this user’s leave requests */
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

    /** Show the form to create a new leave request */
    public function create()
    {
        return view('leaves.create', [
            'types' => $this->types,
        ]);
    }

    /** Store a newly created leave request */
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
            ->with('success','Leave request submitted.');
    }

    /** Show the modal data for editing (if you need it) */
    public function edit(LeaveRequest $leave)
    {
        abort_unless($leave->user_id === auth()->id(), 403);
        abort_unless($leave->status === 'pending', 403);

        return response()->json([
            'leave' => $leave,
            'types' => $this->types,
        ]);
    }

    /** Update an existing leave request */
    public function update(Request $request, LeaveRequest $leave)
    {
        abort_unless($leave->user_id === auth()->id(), 403);
        abort_unless($leave->status === 'pending', 403);

        $data = $request->validate([
            'leave_type' => 'required|in:'.implode(',', array_keys($this->types)),
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'nullable|string|max:1000',
        ]);

        $leave->update($data);

        return redirect()
            ->route('leaves.index')
            ->with('success','Leave request updated.');
    }

    /** Delete a pending leave request */
    public function destroy(LeaveRequest $leave)
    {
        abort_unless($leave->user_id === auth()->id(), 403);
        abort_unless($leave->status === 'pending', 403);

        $leave->delete();

        return redirect()
            ->route('leaves.index')
            ->with('success','Leave request deleted.');
    }
}
