<?php

namespace App\Http\Controllers;

use App\Models\DisciplinaryAction;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class DisciplinaryActionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Table-only page with optional filters */
    public function index(Request $request)
    {
        $query = DisciplinaryAction::with(['employee', 'issuer'])->latest();

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('type')) {
            $query->where('action_type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $actions   = $query->paginate(15)->withQueryString();
        $employees = Employee::orderBy('name')->pluck('name', 'id');

        return view('discipline.index', compact('actions', 'employees'));
    }

    /** Separate create form page */
    public function create()
    {
        $employees = Employee::orderBy('name')->pluck('name', 'id');
        return view('discipline.create', compact('employees'));
    }

    /** Save a new violation / suspension */
    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'action_type' => 'required|in:violation,suspension',
            'category'    => 'nullable|string|max:255',
            'severity'    => 'required|in:minor,major,critical',
            'points'      => 'nullable|integer|min:0|max:100',
            'reason'      => 'required|string',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'notes'       => 'nullable|string',
        ]);

        if ($data['action_type'] === 'suspension') {
            $request->validate([
                'start_date' => 'required|date',
                'end_date'   => 'required|date|after_or_equal:start_date',
            ]);
        } else {
            $data['start_date'] = null;
            $data['end_date']   = null;
        }

        foreach (['category','notes'] as $k) {
            if (!isset($data[$k]) || $data[$k] === '') $data[$k] = null;
        }
        if (!isset($data['points']) || $data['points'] === '') $data['points'] = null;

        $data['issued_by'] = auth()->id();
        $data['status']    = 'active';

        try {
            DB::transaction(fn () => DisciplinaryAction::create($data));
            return redirect()->route('discipline.index')->with('success', 'Disciplinary action recorded.');
        } catch (\Throwable $e) {
            Log::error('DisciplinaryAction store failed: '.$e->getMessage());
            return back()->withInput()->with('error', 'Failed to save disciplinary action.');
        }
    }

    public function resolve(DisciplinaryAction $action)
    {
        $action->update(['status' => 'resolved']);
        return back()->with('success', 'Action marked as resolved.');
    }

    public function destroy(DisciplinaryAction $action)
    {
        $action->delete();
        return back()->with('success', 'Disciplinary action deleted.');
    }

    /** Generate certificate-style PDF letter */
    public function pdf(DisciplinaryAction $action)
    {
        $action->load(['employee','issuer']);

        $company = [
            'name'    => 'Asia Textile Mills, Inc.',
            'address' => [
                'Old National Highway, Bgy San Cristobal,',
                'Calamba, Laguna, Philippines',
                '(049) 531 7239 | asiatex84@gmail.com',
            ],
            // absolute path is safest for Dompdf
            'logo'    => public_path('images/asiatex.png'),
        ];

        $data = [
            'action'    => $action,
            'company'   => $company,
            'plant_mgr' => 'Mr. Moises A. Galicha',
        ];

        $pdf = Pdf::loadView('discipline.pdf', $data)->setPaper('a4');

        $name = Str::slug($action->action_type.'-'.$action->id).'.pdf';
        return $pdf->stream($name);
    }
}
