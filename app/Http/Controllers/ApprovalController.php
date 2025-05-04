<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\LeaveRequest;

class ApprovalController extends Controller
{
    /**
     * Show pending users and leave‐requests.
     */
    public function index()
    {
        // 1) Pending user‐account approvals
        $pendingUsers = User::where('status','pending')
                            ->orderBy('created_at','asc')
                            ->get();

        // 2) Pending leave requests
        $pendingLeaves = LeaveRequest::with('user')
                                     ->where('status','pending')
                                     ->orderBy('created_at','asc')
                                     ->get();

        return view('approvals.index', compact('pendingUsers','pendingLeaves'));
    }

    /**
     * Approve a user or a leave.
     *
     * @param  string  $t    'user' or 'leave'
     * @param  int     $id
     */
    public function approve($t, $id)
    {
        if ($t === 'user') {
            $u = User::findOrFail($id);
            $u->status = 'active';
            $u->save();
            $msg = "User {$u->name} approved.";
        }
        elseif ($t === 'leave') {
            $lr = LeaveRequest::findOrFail($id);
            $lr->status = 'approved';
            $lr->save();
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
            $u = User::findOrFail($id);
            $u->delete();
            $msg = "User {$u->name} rejected.";
        }
        elseif ($t === 'leave') {
            $lr = LeaveRequest::findOrFail($id);
            $lr->delete();
            $msg = "Leave request deleted.";
        }
        else {
            abort(404);
        }

        return back()->with('success',$msg);
    }
}
