<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\SssContribution;
use App\Models\PhilhealthContribution;
use App\Models\PagibigContribution;
use App\Models\Loan;
use App\Models\Holiday;
use App\Models\Payslip;
use App\Models\LeaveRequest;
use App\Models\DisciplinaryAction; // ⬅️ NEW
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use PDF;

class PayrollController extends Controller
{
    /** Which leave types are treated as PAID. */
    protected array $paidLeaveKeys = ['service','maternity','paternity'];

    /** Return holiday dates as Y-m-d strings. */
    private function holidaySet(): array
    {
        return Holiday::query()
            ->pluck('date')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->values()
            ->toArray();
    }

    /** Map of holidays between two dates. */
    private function holidaySetMap(Carbon $start, Carbon $end): array
    {
        return Holiday::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->reduce(function($acc,$h){
                $acc[Carbon::parse($h->date)->toDateString()] = $h->name;
                return $acc;
            }, []);
    }

    /** Build leave index: employee_id → date(Y-m-d) → collection(LeaveRequest). */
    private function leaveIndex(Carbon $start, Carbon $end)
    {
        $leaves = LeaveRequest::where('status','approved')
            ->whereDate('start_date','<=',$end->toDateString())
            ->whereDate('end_date','>=',$start->toDateString())
            ->get();

        $idx = collect();
        foreach ($leaves as $lv) {
            $from = Carbon::parse($lv->start_date)->max($start);
            $to   = Carbon::parse($lv->end_date)->min($end);
            foreach (CarbonPeriod::create($from, $to) as $d) {
                $idx[$lv->employee_id][$d->toDateString()] = collect([$lv]);
            }
        }
        return $idx;
    }

    /** NEW: Build discipline overlays for a window. */
    private function disciplineIndex(Carbon $start, Carbon $end, $empIds)
    {
        $acts = DisciplinaryAction::whereIn('employee_id', $empIds)
            ->where(function ($q) use ($start, $end) {
                $q->where(function ($qq) use ($start, $end) {
                    $qq->where('action_type', 'suspension')
                       ->whereDate('start_date', '<=', $end->toDateString())
                       ->whereDate('end_date',   '>=', $start->toDateString());
                })->orWhere(function ($qq) use ($start, $end) {
                    $qq->where('action_type', 'violation')
                       ->whereDate(\DB::raw('COALESCE(start_date, created_at)'), '>=', $start->toDateString())
                       ->whereDate(\DB::raw('COALESCE(start_date, created_at)'), '<=', $end->toDateString());
                });
            })
            ->get();

        $susp = [];
        $viol = [];
        foreach ($acts as $a) {
            if ($a->action_type === 'suspension' && $a->start_date && $a->end_date) {
                for ($d = $a->start_date->copy(); $d->lte($a->end_date); $d->addDay()) {
                    if ($d->lt($start) || $d->gt($end)) continue;
                    $susp[$a->employee_id][$d->toDateString()] = $a;
                }
            } else {
                $d = optional($a->start_date)->toDateString() ?? $a->created_at->toDateString();
                $viol[$a->employee_id][$d][] = $a;
            }
        }
        return ['suspensions' => $susp, 'violations' => $viol];
    }

    /** Convert minutes late to hours rounded up to 0.25h increments, capped at 23.75. */
    private function lateHoursFromMinutes(int $mins): float
    {
        if ($mins <= 0) return 0.0;
        $hours = ceil($mins / 15) * 0.25;
        return min($hours, 23.75);
    }

    /** 1) Calendar. */
    public function calendar(Request $request)
    {
        $search = $request->input('search', '');
        $month  = $request->input('month', Carbon::now()->format('Y-m'));
        $start  = Carbon::parse("{$month}-01")->startOfMonth();
        $end    = (clone $start)->endOfMonth();

        $employees = Employee::where('status', 'active')
            ->when($search, fn ($q, $s) =>
                $q->where('employee_code','like',"%{$s}%")
                  ->orWhere('name','like',"%{$s}%")
            )
            ->with('schedule')
            ->orderBy('name')
            ->paginate(20);

        $attendance = Attendance::whereIn('employee_id', $employees->pluck('id'))
            ->whereBetween('time_in', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy('employee_id')
            ->map(fn ($g) => $g->groupBy(fn ($r) => Carbon::parse($r->time_in)->toDateString()));

        $leaveIndex = $this->leaveIndex($start, $end);
        $holidays   = $this->holidaySetMap($start, $end);

        // ⬅️ pass discipline overlays
        $discipline = $this->disciplineIndex($start, $end, $employees->pluck('id'));

        return view('payroll.calendar', compact(
            'employees','attendance','start','end','search','month','leaveIndex','holidays','discipline'
        ));
    }

    /** 2) Daily snapshot list (unchanged logic for amounts) */
    public function index(Request $request)
    {
        // (existing body unchanged)
        // ...
        // The daily net pay summary remains the same; discipline is for visibility elsewhere.
        $date   = $request->input('date', Carbon::today()->toDateString());
        $search = $request->input('search','');

        $employees = Employee::where('status','active')
            ->when($search, fn ($q,$s) =>
                $q->where('employee_code','like',"%{$s}%")
                  ->orWhere('name','like',"%{$s}%")
            )
            ->with(['designation','schedule'])
            ->orderBy('name')
            ->get();

        $sssBr     = Cache::remember('sss_brackets',     now()->addDay(), fn()=> SssContribution::all());
        $philBr    = Cache::remember('phil_brackets',    now()->addDay(), fn()=> PhilhealthContribution::all());
        $pagibigBr = Cache::remember('pagibig_brackets', now()->addDay(), fn()=> PagibigContribution::all());
        $findBr    = fn($col,$gross)=>
            $col->first(fn($b)=> $b->range_min <= $gross && $b->range_max >= $gross);

        $isHoliday = Holiday::whereDate('date', $date)->exists();

        $rows = [];

        foreach ($employees as $emp) {
            $rateHr = (float) ($emp->designation->rate_per_hour ?? 0);
            $att    = Attendance::where('employee_id',$emp->id)->whereDate('time_in',$date)->first();

            $hrs = $schedH = $ndHrs = 0;
            $lateDeduction = 0.0;
            $lastOut = null;

            if ($att && $att->time_out) {
                $in  = Carbon::parse($att->time_in);
                $out = Carbon::parse($att->time_out);
                if ($out->lt($in)) $out->addDay();

                $hrs = $in->diffInHours($out);
                $lastOut = $out->copy();

                $sIn = $sOut = null;
                if ($sch = $emp->schedule) {
                    $sIn  = Carbon::parse($sch->time_in)->setDate($in->year,$in->month,$in->day);
                    $sOut = Carbon::parse($sch->time_out)->setDate($in->year,$in->month,$in->day);
                    if ($sOut->lt($sIn)) $sOut->addDay();
                    $schedH = $sIn->diffInHours($sOut);
                }

                $ndStart = $in->copy()->setTime(22,0);
                $ndEnd   = $in->copy()->setTime(6,0)->addDay();
                $startND = $in->gt($ndStart) ? $in  : $ndStart;
                $endND   = $out->lt($ndEnd)  ? $out : $ndEnd;
                if ($endND->gt($startND)) {
                    $ndHrs = (int) floor($startND->diffInMinutes($endND) / 60);
                }

                if (isset($sIn) && $in->gt($sIn)) {
                    $minsLate      = $sIn->diffInMinutes($in);
                    $lateHours     = $this->lateHoursFromMinutes($minsLate);
                    $lateDeduction = round($rateHr * $lateHours, 2);
                }
            }

            if ($schedH === 0 && $emp->schedule) {
                $d = Carbon::parse($date);
                $sIn  = Carbon::parse($emp->schedule->time_in)->setDate($d->year,$d->month,$d->day);
                $sOut = Carbon::parse($emp->schedule->time_out)->setDate($d->year,$d->month,$d->day);
                if ($sOut->lt($sIn)) $sOut->addDay();
                $schedH = $sIn->diffInHours($sOut);
            }

            $loanDed = Carbon::parse($date)->day === 15
                ? (float) Loan::where('employee_id',$emp->id)->where('status','active')->sum('monthly_amount')
                : 0.0;

            $lvKey = LeaveRequest::where('status','approved')
                ->where('employee_id',$emp->id)
                ->whereDate('start_date','<=',$date)
                ->whereDate('end_date','>=',$date)
                ->value('leave_type');
            $paidLeave = in_array($lvKey ?? '', $this->paidLeaveKeys, true);

            $holidayPay = $isHoliday ? round($schedH * $rateHr, 2) : 0.0;

            $regularHrs = min($hrs, $schedH);
            $basePay    = $regularHrs * $rateHr;

            $leavePay = 0.0;
            if ($paidLeave) {
                $leaveHours = max($schedH - $regularHrs, 0);
                $leavePay   = round($leaveHours * $rateHr, 2);
            }

            $otHrs = 0;
            if (isset($sOut) && $lastOut && $lastOut->gt($sOut)) {
                $otMinutes = $sOut->diffInMinutes($lastOut);
                $otHrs     = intdiv($otMinutes, 60);
            }

            $otRate = $isHoliday ? 2.60 : 1.25;
            $otPay  = round($otHrs * $rateHr * $otRate, 2);

            $ndPay  = round($ndHrs * $rateHr * 0.10, 2);

            $grossAll = round($basePay + $leavePay + $holidayPay + $otPay + $ndPay, 2);

            $isLast  = Carbon::parse($date)->isSameDay(Carbon::parse($date)->endOfMonth());
            $sssEmp  = $isLast ? (float) ($findBr($sssBr, $grossAll)->employee_share ?? 0) : 0.0;
            $philEmp = $isLast ? round($grossAll * (($findBr($philBr, $grossAll)->rate_percent ?? 0) / 100) / 2, 2) : 0.0;
            $pagEmp  = $isLast ? (float) ($findBr($pagibigBr, $grossAll)->employee_share ?? 0) : 0.0;

            $totalDed = round($sssEmp + $philEmp + $pagEmp + $lateDeduction + $loanDed, 2);
            $netPay   = round($grossAll - $totalDed, 2);

            $rows[] = [
                'employee_id'   => $emp->id,
                'employee_code' => $emp->employee_code,
                'employee_name' => $emp->name,
                'net_pay'       => $netPay,
            ];
        }

        $page    = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $slice   = array_slice($rows, ($page - 1) * $perPage, $perPage);
        $paginator = new LengthAwarePaginator(
            $slice, count($rows), $perPage, $page,
            ['path'=>route('payroll.index'),'query'=>$request->query()]
        );

        $loans = Loan::with(['employee','loanType','plan'])
            ->when($search, fn($q,$s)=>
                $q->where('reference_no','like',"%{$s}%")
                  ->orWhereHas('employee', fn($q2)=>
                      $q2->where('name','like',"%{$s}%")
                         ->orWhere('employee_code','like',"%{$s}%")
                  )
            )
            ->latest('released_at')
            ->get();

        return view('payroll.index', [
            'rows'   => $paginator,
            'date'   => $date,
            'search' => $search,
            'loans'  => $loans,
        ]);
    }

    /**
     * 3) Payslip: annotate days with "disc" (Suspension / Violation)
     */
    public function show($id, Request $request)
    {
        $month    = $request->input('month', Carbon::now()->format('Y-m'));
        $start    = Carbon::parse("{$month}-01")->startOfMonth();
        $end      = (clone $start)->endOfMonth();
        $employee = Employee::with(['designation','schedule'])->findOrFail($id);
        $rateHr   = (float) ($employee->designation->rate_per_hour ?? 0);

        $sssBr     = Cache::remember('sss_brackets',     now()->addDay(), fn()=> SssContribution::all());
        $philBr    = Cache::remember('phil_brackets',    now()->addDay(), fn()=> PhilhealthContribution::all());
        $pagibigBr = Cache::remember('pagibig_brackets', now()->addDay(), fn()=> PagibigContribution::all());
        $findBr    = fn($col,$gross)=>
            $col->first(fn($b)=> $b->range_min <= $gross && $b->range_max >= $gross);

        $holidaySet  = $this->holidaySet();
        $monthlyLoan = (float) Loan::where('employee_id',$id)->where('status','active')->sum('monthly_amount');

        // NEW: discipline overlay for the month for this employee
        $disc = $this->disciplineIndex($start, $end, collect([$id]));
        $isSuspended = function($dateStr) use ($disc, $id) {
            return isset($disc['suspensions'][$id][$dateStr]);
        };
        $violationOn = function($dateStr) use ($disc, $id) {
            return !empty($disc['violations'][$id][$dateStr] ?? []);
        };

        $firstRows  = [];
        $secondRows = [];

        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $dateStr    = $day->toDateString();
            $isHoliday  = in_array($dateStr, $holidaySet, true);

            $atts = Attendance::where('employee_id',$id)
                        ->whereDate('time_in',$dateStr)
                        ->orderBy('time_in')
                        ->get();

            $hrs = $schedH = $ndHrs = 0;
            $lateDeduction = 0.0;
            $lastOut = null;

            $sIn = $sOut = null;
            if ($sch = $employee->schedule) {
                $sIn  = Carbon::parse($sch->time_in)->setDate($day->year,$day->month,$day->day);
                $sOut = Carbon::parse($sch->time_out)->setDate($day->year,$day->month,$day->day);
                if ($sOut->lt($sIn)) $sOut->addDay();
                $schedH = $sIn->diffInHours($sOut);
            }

            // If suspended this day, force zeroed hours/pay (visibility handled via 'disc')
            $discLabel = null;
            if ($isSuspended($dateStr)) {
                $discLabel = 'Suspension';
                $hrs = 0; $ndHrs = 0; $lastOut = null;
            } else {
                if ($atts->count()) {
                    $firstIn = Carbon::parse($atts->first()->time_in);

                    foreach ($atts as $att) {
                        if (! $att->time_out) continue;

                        $in  = Carbon::parse($att->time_in);
                        $out = Carbon::parse($att->time_out);
                        if ($out->lt($in)) $out->addDay();

                        $hrs += $in->diffInHours($out);
                        if (!$lastOut || $out->gt($lastOut)) $lastOut = $out->copy();

                        $ndStart = $in->copy()->setTime(22,0);
                        $ndEnd   = $in->copy()->setTime(6,0)->addDay();
                        $startND = $in->gt($ndStart) ? $in  : $ndStart;
                        $endND   = $out->lt($ndEnd)  ? $out : $ndEnd;
                        if ($endND->gt($startND)) {
                            $ndHrs += (int) floor($startND->diffInMinutes($endND) / 60);
                        }
                    }

                    if (isset($sIn) && $firstIn->gt($sIn)) {
                        $minsLate      = $sIn->diffInMinutes($firstIn);
                        $lateHours     = $this->lateHoursFromMinutes($minsLate);
                        $lateDeduction = round($rateHr * $lateHours, 2);
                    }
                }
            }

            if (!$discLabel && $violationOn($dateStr)) {
                $discLabel = 'Violation';
            }

            $otHrs = 0;
            if (isset($sOut) && $lastOut && $lastOut->gt($sOut)) {
                $otMinutes = $sOut->diffInMinutes($lastOut);
                $otHrs     = intdiv($otMinutes, 60);
            }

            $holidayPay = $isHoliday ? round($schedH * $rateHr, 2) : 0.0;
            $regularHrs = min($hrs, $schedH);
            $basePay    = $regularHrs * $rateHr;

            $lvKey = LeaveRequest::where('status','approved')
                ->where('employee_id',$id)
                ->whereDate('start_date','<=',$dateStr)
                ->whereDate('end_date','>=',$dateStr)
                ->value('leave_type');

            $leavePay = 0.0;
            if (in_array($lvKey ?? '', $this->paidLeaveKeys, true)) {
                $schedBasis = $schedH > 0 ? $schedH : 8;
                $leaveHours = max($schedBasis - $regularHrs, 0);
                $leavePay   = round($leaveHours * $rateHr, 2);
            }

            $otRate = $isHoliday ? 2.60 : 1.25;
            $otPay  = round($otHrs * $rateHr * $otRate, 2);
            $ndPay  = round($ndHrs * $rateHr * 0.10, 2);

            $gross = round($basePay + $leavePay + $holidayPay + $otPay + $ndPay, 2);

            $loanDed = ($day->day === 15) ? $monthlyLoan : 0.0;

            $row = [
                'date'        => $dateStr,
                'worked_hr'   => number_format($hrs, 0),
                'ot_hr'       => number_format($otHrs, 0),
                'ot_pay'      => number_format($otPay, 2),
                'nd_hr'       => number_format($ndHrs, 0),
                'nd_pay'      => number_format($ndPay, 2),
                'holiday_pay' => number_format($holidayPay, 2),
                'gross'       => number_format($gross, 2),
                'late'        => number_format($lateDeduction, 2),
                'loan'        => number_format($loanDed, 2),
                'govt'        => number_format(0, 2),
                'net'         => number_format(round($gross - ($lateDeduction + $loanDed), 2), 2),

                // raw for month-end govt totals
                '_gross' => $gross,
                '_late'  => $lateDeduction,
                '_loan'  => $loanDed,
                '_govt'  => 0.0,
                '_net'   => round($gross - ($lateDeduction + $loanDed), 2),

                // NEW: discipline label for UI
                'disc'   => $discLabel,
            ];

            if ($day->day <= 15) {
                $firstRows[] = $row;
            } else {
                $secondRows[] = $row;
            }
        }

        // GOVT deductions on last day of month (2nd cutoff total) — unchanged
        if (count($secondRows)) {
            $grossSecond = array_sum(array_map(fn ($r) => $r['_gross'], $secondRows));
            $sss   = (float) ($findBr($sssBr, $grossSecond)->employee_share ?? 0);
            $phil  = round($grossSecond * (($findBr($philBr, $grossSecond)->rate_percent ?? 0)/100)/2, 2);
            $pag   = (float) ($findBr($pagibigBr, $grossSecond)->employee_share ?? 0);
            $govtTotal = round($sss + $phil + $pag, 2);

            $last = count($secondRows) - 1;
            $secondRows[$last]['_govt'] = $govtTotal;
            $secondRows[$last]['govt']  = number_format($govtTotal, 2);
            $secondRows[$last]['_net']  = round(
                $secondRows[$last]['_gross']
                - ($secondRows[$last]['_late'] + $secondRows[$last]['_loan'] + $govtTotal),
                2
            );
            $secondRows[$last]['net'] = number_format($secondRows[$last]['_net'], 2);
        }

        // Helper: sum column
        $sum = fn(array $rows, string $key) => array_sum(array_map(fn($r)=> (float)($r[$key] ?? 0), $rows));

        $firstTotals = [
            'worked_hr'   => (int) $sum($firstRows, 'worked_hr'),
            'ot_hr'       => (int) $sum($firstRows, 'ot_hr'),
            'ot_pay'      => round($sum($firstRows, 'ot_pay'), 2),
            'nd_hr'       => (int) $sum($firstRows, 'nd_hr'),
            'nd_pay'      => round($sum($firstRows, 'nd_pay'), 2),
            'holiday_pay' => round($sum($firstRows, 'holiday_pay'), 2),
            'late'        => round($sum($firstRows, 'late'), 2),
            'gross'       => round($sum($firstRows, 'gross'), 2),
            'loan'        => round($monthlyLoan, 2),
            'govt'        => 0.0,
        ];
        $firstTotals['net'] = round(
            $firstTotals['gross'] - ($firstTotals['late'] + $firstTotals['loan'] + $firstTotals['govt']),
            2
        );

        $grossSecond = $sum($secondRows, 'gross');
        $sss   = $grossSecond > 0 ? (float) ($findBr($sssBr, $grossSecond)->employee_share ?? 0) : 0.0;
        $phil  = $grossSecond > 0 ? round($grossSecond * (($findBr($philBr, $grossSecond)->rate_percent ?? 0)/100)/2, 2) : 0.0;
        $pag   = $grossSecond > 0 ? (float) ($findBr($pagibigBr, $grossSecond)->employee_share ?? 0) : 0.0;
        $govtTotal = round($sss + $phil + $pag, 2);

        $secondTotals = [
            'worked_hr'   => (int) $sum($secondRows, 'worked_hr'),
            'ot_hr'       => (int) $sum($secondRows, 'ot_hr'),
            'ot_pay'      => round($sum($secondRows, 'ot_pay'), 2),
            'nd_hr'       => (int) $sum($secondRows, 'nd_hr'),
            'nd_pay'      => round($sum($secondRows, 'nd_pay'), 2),
            'holiday_pay' => round($sum($secondRows, 'holiday_pay'), 2),
            'late'        => round($sum($secondRows, 'late'), 2),
            'gross'       => round($grossSecond, 2),
            'loan'        => 0.0,
            'govt'        => $govtTotal,
        ];
        $secondTotals['net'] = round(
            $secondTotals['gross'] - ($secondTotals['late'] + $secondTotals['loan'] + $secondTotals['govt']),
            2
        );

        return view('payroll.show', compact(
            'employee','month','firstRows','secondRows','firstTotals','secondTotals'
        ));
    }

    /** 4) Export PDF — unchanged */
    public function exportPdf(Request $request)
    {
        $response = $this->index($request);
        $viewData = $response->getData();
        $rows     = $viewData['rows'];
        $pdf      = PDF::loadView('payroll.pdf', compact('rows'));
        return $pdf->download('PayrollDetails.pdf');
    }

    /** 5) Toggle manual attendance. */
    public function toggleManual(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date'        => 'required|date',
        ]);

        $att = Attendance::firstOrCreate([
            'employee_id' => $request->employee_id,
            'time_in'     => $request->date.' 00:00:00',
        ], ['time_out'=>null]);

        $att->is_manual = ! $att->is_manual;
        $att->save();

        return response()->json(['manual'=>$att->is_manual]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // 6) Manual Payroll (NEW)
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Show manual payroll screen.
     * Now also passes $employees for the Blade select and default period dates.
     */
    public function manual(Request $request)
    {
        $formulas = $this->getFormulaOptions();

        // For your Blade:
        $employees = Employee::whereIn('status', ['active','pending'])
            ->orderBy('name')
            ->get(['id','employee_code','name']);

        $periodStart = Carbon::now()->startOfMonth()->toDateString();
        $periodEnd   = Carbon::now()->toDateString();

        // If the page was returned with a computed result, forward it
        $result = session('manual_result');
        $sticky = session('manual_input');

        return view('payroll.manual', [
            'formulas'    => $formulas,
            'result'      => $result,
            'sticky'      => $sticky,
            'employees'   => $employees,
            'periodStart' => $periodStart,
            'periodEnd'   => $periodEnd,
        ]);
    }

    /**
     * Calculate manual payroll.
     * Supports two modes:
     *  A) Spreadsheet mode (rows_json present)
     *  B) Legacy single-row calculator (kept for backward compatibility).
     */
    public function manualStore(Request $request)
    {
        // ── A) Spreadsheet mode (rows_json present)
        if ($request->filled('rows_json')) {
            $data = $request->validate([
                'employee_id'   => 'required|exists:employees,id',
                'period_start'  => 'required|date',
                'period_end'    => 'required|date|after_or_equal:period_start',
                'rate_per_hour' => 'nullable|numeric|min:0',
                'rows_json'     => 'required|string',
            ]);

            $employee = Employee::with('designation','user')->findOrFail($data['employee_id']);

            $rate = (float) ($data['rate_per_hour'] ?? 0);
            if ($rate <= 0) {
                $rate = (float) (optional($employee->designation)->rate_per_hour ?? 0);
            }

            // Decode rows and compute totals
            $rows = json_decode($data['rows_json'], true);
            if (!is_array($rows)) {
                return back()->withInput()->with('error', 'Rows payload is invalid.');
            }

            // Map for OT keys used by the Blade
            $otMap = [
                'OT_REG'     => 1.25,
                'OT_REST'    => 1.30,
                'OT_HOLIDAY' => 2.60,
            ];
            $ND_RATE = 0.10;

            $tWorked = 0.0;
            $tOT_Hrs = 0.0;
            $tOT_Pay = 0.0;
            $tGross  = 0.0;
            $tDed    = 0.0;
            $tNet    = 0.0;

            foreach ($rows as $r) {
                $worked   = (float) ($r['worked_hr']   ?? 0);
                $otHr     = (float) ($r['ot_hr']       ?? 0);
                $otKey    = (string)($r['ot_key']      ?? 'OT_REG');
                $ndHr     = (float) ($r['nd_hr']       ?? 0);
                // In your UI, Holiday field is an amount, not hours:
                $holidayA = (float) ($r['holiday_pay'] ?? 0);
                $late     = (float) ($r['late']        ?? 0);
                $loan     = (float) ($r['loan']        ?? 0);
                $govt     = (float) ($r['govt']        ?? 0);

                $basePay = $worked * $rate;
                $otPay   = $otHr * $rate * ($otMap[$otKey] ?? 1.25);
                $ndPay   = $ndHr * $rate * $ND_RATE;

                $gross = $basePay + $otPay + $ndPay + $holidayA;
                $ded   = $late + $loan + $govt;
                $net   = $gross - $ded;

                $tWorked += $worked;
                $tOT_Hrs += $otHr;
                $tOT_Pay += $otPay;
                $tGross  += $gross;
                $tDed    += $ded;
                $tNet    += $net;
            }

            // Persist as a Payslip for that employee's user (if exists)
            if ($employee->user) {
                Payslip::create([
                    'user_id'      => $employee->user->id,
                    'period_start' => $data['period_start'],
                    'period_end'   => $data['period_end'],
                    'worked_hours' => round($tWorked, 2),
                    'ot_hours'     => round($tOT_Hrs, 2),
                    'ot_pay'       => round($tOT_Pay, 2),
                    'deductions'   => round($tDed, 2),
                    'gross_amount' => round($tGross, 2),
                    'net_amount'   => round($tNet, 2),
                ]);
            }

            return back()->with('success', 'Manual payslip saved.');
        }

        // ── B) Legacy single-row calculator (kept to not break old flow)
        $keys = implode(',', array_keys($this->getFormulaOptions()));

        $data = $request->validate([
            'employee_id'    => 'required|exists:employees,id',
            'date'           => 'required|date',
            'worked_hours'   => 'nullable|numeric|min:0',
            'ot_hours'       => 'nullable|numeric|min:0',
            'nd_hours'       => 'nullable|numeric|min:0',
            'holiday_hours'  => 'nullable|numeric|min:0',
            'ot_formula'     => "required|in:{$keys}",
            'holiday_formula'=> "nullable|in:{$keys}",
            'late_deduction' => 'nullable|numeric|min:0',
            'loan_deduction' => 'nullable|numeric|min:0',
            'govt_mode'      => 'required|in:auto,custom',
            'govt_custom'    => 'nullable|numeric|min:0',
            'note'           => 'nullable|string|max:1000',
        ]);

        $employee = Employee::with('designation')->findOrFail($data['employee_id']);

        $result = $this->computeManual($employee, $data);

        // Bounce back to the manual page with the calculated result
        return redirect()
            ->route('payroll.manual')
            ->with('manual_result', $result)
            ->with('manual_input', $data);
    }

    /**
     * AJAX: search employees by name/code OR fetch one by id.
     * - ?id=123 → { id, code, name, rate, sched: {in,out} }
     * - ?q=term → [ … up to 10 matches … ]
     */
    public function employeeLookup(Request $request)
    {
        if ($request->filled('id')) {
            $e = Employee::with(['designation','schedule'])
                ->find($request->input('id'));

            if (!$e) {
                return response()->json(['message' => 'Not found'], 404);
            }

            return response()->json([
                'id'   => $e->id,
                'code' => $e->employee_code,
                'name' => $e->name,
                'rate_per_hour' => (float) optional($e->designation)->rate_per_hour ?: 0,
                'sched' => [
                    'in'  => optional($e->schedule)->time_in,
                    'out' => optional($e->schedule)->time_out,
                ],
            ]);
        }

        $q = trim($request->input('q', ''));
        if ($q === '') {
            return response()->json([]);
        }

        $employees = Employee::with('designation')
            ->whereIn('status', ['active','pending'])
            ->where(function($sub) use ($q){
                $sub->where('name','like',"%{$q}%")
                    ->orWhere('employee_code','like',"%{$q}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(function($e){
                return [
                    'id'     => $e->id,
                    'code'   => $e->employee_code,
                    'name'   => $e->name,
                    'rate'   => (float) optional($e->designation)->rate_per_hour ?: 0,
                    'sched'  => [
                        'in'  => optional($e->schedule)->time_in,
                        'out' => optional($e->schedule)->time_out,
                    ],
                ];
            })
            ->values();

        return response()->json($employees);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Helpers for manual payroll (legacy calculator)
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Options you’ll show in the <select>. Keys are re-used in computeManual().
     */
    private function getFormulaOptions(): array
    {
        return [
            // Overtime presets
            'ot125'        => 'OT ×1.25 (Regular Day)',
            'ot130'        => 'OT ×1.30 (Rest Day/Special)',
            'ot150'        => 'OT ×1.50 (Rest Day OT)',
            'ot200'        => 'OT ×2.00 (Regular Holiday OT)',
            'ot260'        => 'OT ×2.60 (Regular Holiday OT, 30% premium)',

            // Holiday base pay presets (multiplied by holiday_hours)
            'holiday130'   => 'Holiday ×1.30 (Special Holiday)',
            'holiday200'   => 'Holiday ×2.00 (Regular Holiday)',
        ];
    }

    /**
     * Core calculator used by legacy manual calculator.
     */
    private function computeManual(Employee $employee, array $data): array
    {
        $rate = (float) (optional($employee->designation)->rate_per_hour ?? 0);
        $worked  = (float) ($data['worked_hours']  ?? 0);
        $otHrs   = (float) ($data['ot_hours']      ?? 0);
        $ndHrs   = (float) ($data['nd_hours']      ?? 0);
        $holHrs  = (float) ($data['holiday_hours'] ?? 0);

        // Base
        $basePay = round($worked * $rate, 2);

        // OT
        $otMultiplier = match ($data['ot_formula']) {
            'ot130' => 1.30,
            'ot150' => 1.50,
            'ot200' => 2.00,
            'ot260' => 2.60,
            default => 1.25, // ot125
        };
        $otPay = round($otHrs * $rate * $otMultiplier, 2);

        // ND (10%)
        $ndPay = round($ndHrs * $rate * 0.10, 2);

        // Holiday base pay (if given)
        $holidayMultiplier = match ($data['holiday_formula'] ?? null) {
            'holiday200' => 2.00,
            'holiday130' => 1.30,
            default      => 0.0,  // if no formula provided, treat as 0
        };
        $holidayPay = round($holHrs * $rate * $holidayMultiplier, 2);

        $gross = round($basePay + $otPay + $ndPay + $holidayPay, 2);

        // Government deductions (optional auto)
        [$sss, $phil, $pagibig] = [0.0, 0.0, 0.0];
        if (($data['govt_mode'] ?? 'custom') === 'auto') {
            [$sss, $phil, $pagibig] = $this->computeGovtOnGross($gross);
        }
        $govt = ($data['govt_mode'] === 'custom')
            ? (float) ($data['govt_custom'] ?? 0)
            : round($sss + $phil + $pagibig, 2);

        $late = (float) ($data['late_deduction'] ?? 0);
        $loan = (float) ($data['loan_deduction'] ?? 0);

        $totalDed = round($govt + $late + $loan, 2);
        $net      = round($gross - $totalDed, 2);

        return [
            'date'          => $data['date'],
            'employee'      => [
                'id'    => $employee->id,
                'code'  => $employee->employee_code,
                'name'  => $employee->name,
                'rate'  => $rate,
            ],
            'components'    => [
                'base_pay'     => $basePay,
                'ot_hours'     => $otHrs,
                'ot_multiplier'=> $otMultiplier,
                'ot_pay'       => $otPay,
                'nd_hours'     => $ndHrs,
                'nd_pay'       => $ndPay,
                'holiday_hours'=> $holHrs,
                'holiday_mult' => $holidayMultiplier,
                'holiday_pay'  => $holidayPay,
            ],
            'gross'         => $gross,
            'deductions'    => [
                'govt'   => $govt,
                'sss'    => $sss,
                'phil'   => $phil,
                'pagibig'=> $pagibig,
                'late'   => $late,
                'loan'   => $loan,
            ],
            'net'           => $net,
            'note'          => $data['note'] ?? null,
        ];
    }

    /**
     * Compute SSS/PhilHealth/Pag-IBIG from a gross amount using your brackets.
     * Returns [sss, philhealth, pagibig].
     */
    private function computeGovtOnGross(float $gross): array
    {
        $sssBr     = Cache::remember('sss_brackets',     now()->addDay(), fn()=> SssContribution::all());
        $philBr    = Cache::remember('phil_brackets',    now()->addDay(), fn()=> PhilhealthContribution::all());
        $pagibigBr = Cache::remember('pagibig_brackets', now()->addDay(), fn()=> PagibigContribution::all());

        $findBr = fn($col,$g)=> $col->first(fn($b)=> $b->range_min <= $g && $b->range_max >= $g);

        $sss   = (float) ($findBr($sssBr, $gross)->employee_share ?? 0);
        $phil  = round($gross * (($findBr($philBr, $gross)->rate_percent ?? 0)/100) / 2, 2);
        $pag   = (float) ($findBr($pagibigBr, $gross)->employee_share ?? 0);

        return [$sss, $phil, $pag];
    }

     public function reportPayslips(Request $request)
    {
        $from = $request->query('from', Carbon::now()->startOfMonth()->toDateString());
        $to   = $request->query('to',   Carbon::now()->endOfMonth()->toDateString());

        $employees = Employee::query()
            ->withCount(['attendances as days_worked' => function ($q) use ($from, $to) {
                $q->whereDate('time_in', '>=', $from)
                  ->whereDate('time_in', '<=', $to)
                  ->whereNotNull('time_out');
            }])
            ->orderBy('name')
            ->paginate(20)
            ->appends(['from' => $from, 'to' => $to]);

        return view('reports.payslips', compact('employees','from','to'));
    }

    /**
     * Download one employee's payslip for any date range:
     * /reports/payslips/{employee}/download?from=YYYY-MM-DD&to=YYYY-MM-DD
     */
    public function downloadPayslipRange(Employee $employee, Request $request)
    {
        $from = Carbon::parse($request->query('from', Carbon::now()->startOfMonth()->toDateString()))->startOfDay();
        $to   = Carbon::parse($request->query('to',   Carbon::now()->endOfMonth()->toDateString()))->endOfDay();

        $rateHr = (float) (optional($employee->designation)->rate_per_hour ?? 0);

        $monthlyLoan = (float) Loan::where('employee_id',$employee->id)
            ->where('status','active')
            ->sum('monthly_amount');

        $fifteenths = CarbonPeriod::create($from, $to)
            ->filter(fn($d)=> (int)$d->day === 15)->count();
        $loanDed = $monthlyLoan * $fifteenths;

        $holidays = Holiday::whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->keyBy(fn($h)=> Carbon::parse($h->date)->toDateString());

        $workedHrs = 0;
        $otHrs = 0;
        $ndHrs = 0;
        $holidayPay = 0.0;
        $lateDed = 0.0;

        $sched = $employee->schedule;

        foreach (CarbonPeriod::create($from->copy()->startOfDay(), $to->copy()->startOfDay()) as $day) {
            $dateStr = $day->toDateString();
            $isHoliday = $holidays->has($dateStr);

            $atts = Attendance::where('employee_id',$employee->id)
                ->whereDate('time_in',$dateStr)
                ->orderBy('time_in')
                ->get();

            $hrs = 0;
            $nd = 0;
            $latestOut = null;

            $sIn = $sOut = null;
            $schedH = 0;
            if ($sched) {
                $sIn  = Carbon::parse($sched->time_in)->setDate($day->year,$day->month,$day->day);
                $sOut = Carbon::parse($sched->time_out)->setDate($day->year,$day->month,$day->day);
                if ($sOut->lt($sIn)) $sOut->addDay();
                $schedH = $sIn->diffInHours($sOut);
            }

            if ($atts->count()) {
                $firstIn = Carbon::parse($atts->first()->time_in);

                foreach ($atts as $att) {
                    if (! $att->time_out) continue;

                    $in  = Carbon::parse($att->time_in);
                    $out = Carbon::parse($att->time_out);
                    if ($out->lt($in)) $out->addDay();

                    $hrs += $in->diffInHours($out);

                    if (!$latestOut || $out->gt($latestOut)) $latestOut = $out->copy();

                    $ndStart = $in->copy()->setTime(22,0);
                    $ndEnd   = $in->copy()->setTime(6,0)->addDay();
                    $startND = $in->gt($ndStart) ? $in  : $ndStart;
                    $endND   = $out->lt($ndEnd)  ? $out : $ndEnd;
                    if ($endND->gt($startND)) {
                        $nd += (int) floor($startND->diffInMinutes($endND) / 60);
                    }
                }

                if (isset($sIn) && $firstIn->gt($sIn)) {
                    $minsLate  = $sIn->diffInMinutes($firstIn);
                    $lateHours = $this->lateHoursFromMinutes($minsLate);
                    $lateDed  += round($rateHr * $lateHours, 2);
                }
            }

            if ($isHoliday && $schedH > 0) {
                $holidayPay += round($schedH * $rateHr, 2);
            }

            if (isset($sOut) && $latestOut && $latestOut->gt($sOut)) {
                $otMinutes = $sOut->diffInMinutes($latestOut);
                $otHrs    += intdiv($otMinutes, 60);
            }

            $workedHrs += $hrs;
            $ndHrs     += $nd;
        }

        $basePay = round($workedHrs * $rateHr, 2);
        $otPay   = round($otHrs * $rateHr * 1.25, 2);
        $ndPay   = round($ndHrs * $rateHr * 0.10, 2);

        $gross   = round($basePay + $otPay + $ndPay + $holidayPay, 2);

        $includesMonthEnd = $to->isSameDay((clone $to)->endOfMonth());
        [$sss, $phil, $pag] = $includesMonthEnd ? $this->computeGovtOnGross($gross) : [0.0, 0.0, 0.0];

        $deductions = round($lateDed + $loanDed + $sss + $phil + $pag, 2);
        $net        = round($gross - $deductions, 2);

        $data = [
            'employee'      => $employee,
            'from'          => $from->toDateString(),
            'to'            => $to->toDateString(),
            'rate_hr'       => $rateHr,
            'worked_hours'  => $workedHrs,
            'ot_hours'      => $otHrs,
            'nd_hours'      => $ndHrs,
            'base_pay'      => $basePay,
            'ot_pay'        => $otPay,
            'nd_pay'        => $ndPay,
            'holiday_pay'   => $holidayPay,
            'gross'         => $gross,
            'late'          => $lateDed,
            'loan'          => $loanDed,
            'sss'           => $sss,
            'phil'          => $phil,
            'pag'           => $pag,
            'deductions'    => $deductions,
            'net'           => $net,
        ];

        $pdf = PDF::loadView('reports.pdf.payslip_single', $data)->setPaper('A4','portrait');

        $file = sprintf(
            'Payslip_%s_%s_to_%s.pdf',
            Str::slug($employee->name ?? $employee->employee_code, '_'),
            $from->toDateString(),
            $to->toDateString()
        );

        return $pdf->download($file);
    }
}
