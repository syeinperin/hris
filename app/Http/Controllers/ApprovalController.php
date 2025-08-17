<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Approval;
use App\Models\User;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveAllocation;

class ApprovalController extends Controller
{
    public function index()
    {
        // Pending user approvals
        $pendingUsers = Approval::with('approvable')
            ->where('approvable_type', User::class)
            ->where('status', 'pending')
            ->latest()
            ->get();

        // Pending leave approvals (use the actual model: LeaveRequest)
        $pendingLeaves = Approval::with(['approvable.user'])
            ->where('approvable_type', LeaveRequest::class)
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('approvals.index', compact('pendingUsers', 'pendingLeaves'));
    }

    private function resolveType(string $type): string
    {
        return match ($type) {
            'user'  => User::class,
            'leave' => LeaveRequest::class,
            default => abort(404),
        };
    }

    /** Shared status writer for approve()/destroy(). */
    private function setApprovalStatus(string $type, int $id, string $status)
    {
        $modelClass = $this->resolveType($type);

        $approval = Approval::where('approvable_type', $modelClass)
            ->where('approvable_id', $id)
            ->firstOrFail();

        $approval->update([
            'status'      => $status,
            'approver_id' => Auth::id(),
        ]);

        if ($modelClass === User::class) {
            $user = User::findOrFail($id);
            $user->update(['status' => $status === 'approved' ? 'active' : 'rejected']);
            $user->employee?->update(['status' => $status === 'approved' ? 'active' : 'inactive']);

            if ($status === 'approved') {
                $this->seedEmployeeAllocations($user, now()->year);
            }
        } elseif ($modelClass === LeaveRequest::class) {
            $leave = LeaveRequest::findOrFail($id);
            $leave->update(['status' => $status]);
        }

        return back()->with(
            $status === 'approved' ? 'success' : 'warning',
            $status === 'approved' ? 'Approved successfully.' : 'Request rejected.'
        );
    }

    public function approve(string $type, int $id)
    {
        return $this->setApprovalStatus($type, $id, 'approved');
    }

    /** Matched to Route::delete('approvals/{t}/{id}', ... 'destroy') */
    public function destroy(string $type, int $id)
    {
        return $this->setApprovalStatus($type, $id, 'rejected');
    }

    private function seedEmployeeAllocations(User $user, int $year): void
    {
        $employee = $user->employee;
        if (! $employee) return;

        $gender = strtolower((string) $employee->gender);

        $types = LeaveType::where('is_active', true)
            ->when($gender === 'male',   fn($q) => $q->where('key', '!=', 'maternity'))
            ->when($gender === 'female', fn($q) => $q->where('key', '!=', 'paternity'))
            ->get(['id', 'default_days']);

        foreach ($types as $type) {
            LeaveAllocation::firstOrCreate(
                [
                    'leave_type_id' => $type->id,
                    'employee_id'   => $employee->id,
                    'year'          => $year,
                ],
                [
                    'days_allocated' => (int) ($type->default_days ?? 0),
                    'days_used'      => 0,
                ]
            );
        }
    }
}
