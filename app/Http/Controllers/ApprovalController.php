<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveAllocation;
use Carbon\Carbon;

class ApprovalController extends Controller
{
    /**
     * Show pending users and leave requests.
     */
    public function index()
    {
        $pendingUsers  = User::where('status','pending')->oldest()->get();
        $pendingLeaves = LeaveRequest::with('user')
                            ->where('status','pending')
                            ->oldest()
                            ->get();

        return view('approvals.index', compact('pendingUsers','pendingLeaves'));
    }

    /**
     * Approve a user or a leave.
     *
     * @param string $t   'user' or 'leave'
     * @param int    $id
     */
    public function approve($t, $id)
    {
        if ($t === 'user') {
            $u = User::findOrFail($id);
            $u->update(['status'=>'active']);
            $msg = "User {$u->name} approved.";
        }
        elseif ($t === 'leave') {
            $lr = LeaveRequest::findOrFail($id);

            if ($lr->status !== 'pending') {
                return back()->with('error','This leave is no longer pending.');
            }

            // 1) Mark approved
            $lr->update(['status'=>'approved']);

            // 2) Adjust allocation for just three keys
            $map = [
                'service'   => 'Service Incentive Leave',
                'maternity' => 'Maternity Leave',
                'paternity' => 'Paternity Leave',
            ];

            if ($label = ($map[$lr->leave_type] ?? null)) {
                $type = LeaveType::where('name', $label)->first();
                if ($type && ($emp = $lr->user->employee)) {
                    $year = Carbon::parse($lr->start_date)->year;
                    $days = Carbon::parse($lr->start_date)
                              ->diffInDays(Carbon::parse($lr->end_date)) + 1;

                    $alloc = LeaveAllocation::firstOrCreate(
                        [
                            'employee_id'   => $emp->id,
                            'leave_type_id' => $type->id,
                            'year'          => $year,
                        ],
                        [
                            'days_allocated' => $type->default_days,
                            'days_used'      => 0,
                        ]
                    );

                    $alloc->increment('days_used', $days);
                }
            }

            $msg = "Leave for {$lr->user->name} approved.";
        }
        else {
            abort(404);
        }

        return back()->with('success',$msg);
    }

    /**
     * Reject / delete a user or a leave.
     */
    public function destroy($t, $id)
    {
        if ($t === 'user') {
            User::findOrFail($id)->delete();
            $msg = "User rejected.";
        }
        elseif ($t === 'leave') {
            $lr = LeaveRequest::findOrFail($id);
            if ($lr->status==='pending') {
                $lr->delete();
                $msg = "Leave request deleted.";
            } else {
                return back()->with('error','Cannot reject a processed request.');
            }
        }
        else {
            abort(404);
        }

        return back()->with('success',$msg);
    }
}
