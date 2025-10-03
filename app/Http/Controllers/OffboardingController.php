<?php

namespace App\Http\Controllers;

use App\Models\Offboarding;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OffboardingController extends Controller
{
    public function index(Request $request)
    {
        $q = Offboarding::with(['employee.department','employee.designation'])
            ->when($request->filled('status'), fn($qq) => $qq->where('status', $request->status))
            ->when($request->filled('type'),   fn($qq) => $qq->where('type',   $request->type))
            ->orderByDesc('id');

        $offboardings = $q->paginate(12)->withQueryString();

        return view('offboarding.index', [
            'offboardings' => $offboardings,
        ]);
    }

    public function create()
    {
        $employees = Employee::active()
            ->orderBy('name')
            ->get(['id','employee_code','name']);

        return view('offboarding.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id'               => 'required|exists:employees,id',
            'type'                      => 'required|in:resignation,termination,endo,retirement,other',
            'effective_date'            => 'nullable|date',
            'reason'                    => 'nullable|string|max:255',
            'allow_portal_access_until' => 'nullable|date|after_or_equal:today',
            'company_asset_returned'    => 'nullable|boolean',
            'separation_notes'          => 'nullable|string',
        ]);

        $data['company_asset_returned'] = (bool) ($data['company_asset_returned'] ?? false);
        $data['status']   = 'draft';
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        $off = Offboarding::create($data);

        return redirect()->route('offboarding.show', $off)->with('success', 'Offboarding draft created.');
    }

    public function show(Offboarding $offboarding)
    {
        $offboarding->load(['employee.department','employee.designation','creator','updater']);
        return view('offboarding.show', compact('offboarding'));
    }

    public function edit(Offboarding $offboarding)
    {
        abort_if($offboarding->isFinal(), 403);
        $employees = Employee::orderBy('name')->get(['id','employee_code','name']);
        return view('offboarding.edit', compact('offboarding','employees'));
    }

    public function update(Request $request, Offboarding $offboarding)
    {
        abort_if($offboarding->isFinal(), 403);

        $data = $request->validate([
            'employee_id'               => 'required|exists:employees,id',
            'type'                      => 'required|in:resignation,termination,endo,retirement,other',
            'status'                    => 'required|in:draft,pending_clearance,scheduled,awaiting_approvals,completed,cancelled',
            'effective_date'            => 'nullable|date',
            'reason'                    => 'nullable|string|max:255',
            'allow_portal_access_until' => 'nullable|date|after_or_equal:today',
            'company_asset_returned'    => 'nullable|boolean',
            'separation_notes'          => 'nullable|string',
        ]);

        $data['company_asset_returned'] = (bool) ($data['company_asset_returned'] ?? false);
        $data['updated_by'] = auth()->id();

        DB::transaction(function () use ($offboarding, $data) {
            $offboarding->update($data);

            // If completed → sync Employee status/dates
            if ($offboarding->status === 'completed') {
                $emp = $offboarding->employee()->lockForUpdate()->first();
                if ($emp) {
                    $update = [
                        'status'                 => 'inactive',
                        'employment_end_date'    => $data['effective_date'] ?? $offboarding->effective_date ?? now()->toDateString(),
                    ];

                    if ($this->Schema()->hasColumn('employees', 'separation_status')) {
                        $update['separation_status'] = 'completed';
                    }
                    if ($this->Schema()->hasColumn('employees', 'separation_reason')) {
                        $update['separation_reason'] = $data['reason'] ?? $offboarding->reason;
                    }
                    if ($this->Schema()->hasColumn('employees', 'allow_portal_access_until')) {
                        $update['allow_portal_access_until'] = $data['allow_portal_access_until'] ?? null;
                    }
                    if ($this->Schema()->hasColumn('employees', 'company_asset_returned')) {
                        $update['company_asset_returned'] = $data['company_asset_returned'] ?? false;
                    }
                    if ($this->Schema()->hasColumn('employees', 'separation_notes')) {
                        $update['separation_notes'] = $data['separation_notes'] ?? null;
                    }

                    $emp->update($update);
                }
            }
        });

        return redirect()->route('offboarding.show', $offboarding)->with('success', 'Offboarding updated.');
    }

    public function destroy(Offboarding $offboarding)
    {
        abort_if($offboarding->isFinal(), 403);
        $offboarding->delete();
        return redirect()->route('offboarding.index')->with('warning', 'Offboarding draft removed.');
    }

    // ────────────── Quick transitions ──────────────
    public function markPendingClearance(Offboarding $offboarding)
    {
        abort_if($offboarding->isFinal(), 403);
        $offboarding->update(['status' => 'pending_clearance', 'updated_by' => auth()->id()]);
        return back()->with('success', 'Moved to pending clearance.');
    }

    public function schedule(Request $request, Offboarding $offboarding)
    {
        abort_if($offboarding->isFinal(), 403);
        $data = $request->validate(['effective_date' => 'required|date|after_or_equal:today']);
        $offboarding->update([
            'status'         => 'scheduled',
            'effective_date' => $data['effective_date'],
            'updated_by'     => auth()->id(),
        ]);
        return back()->with('success', 'Separation scheduled.');
    }

    public function complete(Offboarding $offboarding)
    {
        abort_if($offboarding->isFinal(), 403);

        $offboarding->update([
            'status'     => 'completed',
            'updated_by' => auth()->id(),
        ]);

        $emp = $offboarding->employee;
        if ($emp) {
            $emp->update([
                'status'              => 'inactive',
                'employment_end_date' => $offboarding->effective_date ?? now()->toDateString(),
            ]);
        }

        return back()->with('success', 'Offboarding marked as completed.');
    }

    public function cancel(Offboarding $offboarding)
    {
        abort_if($offboarding->isFinal(), 403);

        $offboarding->update([
            'status'     => 'cancelled',
            'updated_by' => auth()->id(),
        ]);

        return back()->with('warning', 'Offboarding was cancelled.');
    }

    // tiny helper for schema check
    private function Schema()
    {
        return app('db.schema');
    }
}
