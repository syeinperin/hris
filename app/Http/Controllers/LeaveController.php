<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Models\Approval;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class LeaveController extends Controller
{
    /**
     * Base map (we’ll filter it per gender).
     */
    protected array $baseTypes = [
        'service'   => 'Service Incentive Leave',
        'maternity' => 'Maternity Leave',
        'paternity' => 'Paternity Leave',
    ];

    private function typesFor(?string $gender, bool $forAdmin = false): array
    {
        if ($forAdmin) {
            return $this->baseTypes;
        }

        $gender = strtolower((string) $gender);
        $types  = $this->baseTypes;

        if ($gender === 'male')   unset($types['maternity']);
        if ($gender === 'female') unset($types['paternity']);

        return $types;
    }

    public function index(Request $request)
    {
        $user  = auth()->user();
        $admin = $user->hasAnyRole(['hr','supervisor']);

        if ($admin) {
            $requests = LeaveRequest::with(['user','employee'])
                ->latest()
                ->paginate(15);
        } else {
            $requests = LeaveRequest::where('employee_id', $user->employee->id)
                ->with('employee')
                ->latest()
                ->paginate(10);
        }

        $types = $this->typesFor(optional($user->employee)->gender, $admin);

        return view('leaves.index', [
            'requests' => $requests,
            'types'    => $types,
        ]);
    }

    public function create()
    {
        $user  = auth()->user();
        $types = $this->typesFor(optional($user->employee)->gender, false);

        return view('leaves.create', compact('types'));
    }

     public function store(Request $request)
    {
        $user  = auth()->user();
        $types = $this->typesFor(optional($user->employee)->gender, false);
        $valid = implode(',', array_keys($types));

        $data = $request->validate([
            'leave_type' => "required|in:{$valid}",
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'nullable|string|max:1000',
        ]);

        $data['user_id']     = $user->id;
        $data['employee_id'] = $user->employee->id;
        $data['status']      = 'pending';

        $leave = LeaveRequest::create($data);

        // Build the Approval payload
        $approvalData = [
            'approvable_id'   => $leave->id,
            'approvable_type' => LeaveRequest::class,
            'status'          => 'pending',
        ];

        // Only set requester_id if the column exists in your DB
        if (Schema::hasColumn('approvals', 'requester_id')) {
            $approvalData['requester_id'] = $user->id;
        }

        Approval::create($approvalData);

        return redirect()->route('leaves.index')->with('success', 'Leave request submitted.');
    }

    public function edit(LeaveRequest $leave)
    {
        $user  = auth()->user();
        $types = $this->typesFor(optional($user->employee)->gender, false);

        return response()->json([
            'leave' => $leave,
            'types' => $types,
        ]);
    }

    public function update(Request $request, LeaveRequest $leave)
    {
        $user  = auth()->user();
        $types = $this->typesFor(optional($user->employee)->gender, false);
        $valid = implode(',', array_keys($types));

        $data = $request->validate([
            'leave_type' => "required|in:{$valid}",
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'nullable|string|max:1000',
        ]);

        $leave->update($data);

        return redirect()->route('leaves.index')->with('success', 'Leave request updated.');
    }

    public function destroy(LeaveRequest $leave)
    {
        $leave->delete();
        return redirect()->route('leaves.index')->with('success', 'Leave request deleted.');
    }
}
