<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

// Models
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\PerformanceEvaluation;
use App\Models\DisciplinaryAction;
use App\Models\Loan;
use App\Models\SssContribution;
use App\Models\PhilhealthContribution;
use App\Models\PagibigContribution;

// PDF
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class ReportController extends Controller
{
    /** Custom paper size (points). 72 pt = 1 in. 4.25in × 11in => 306 × 792. */
    private const PAYSHEET_SIZE = [0, 0, 306, 792];

    /** Small helper for decimal hours between two datetimes (handles cross-midnight). */
    private function hoursBetween(Carbon $in, Carbon $out): float
    {
        if ($out->lt($in)) {
            $out = $out->copy()->addDay();
        }
        // seconds → hours (decimal)
        return round($in->diffInSeconds($out) / 3600, 4);
    }

    /** GET /reports */
    public function index()
    {
        return view('reports.index');
    }

    /** GET /reports/employees */
    public function indexEmployees()
    {
        $employees = Employee::with('department','designation')->orderBy('name')->get();
        return view('reports.employees.index', compact('employees'));
    }

    /** GET /reports/employees/csv */
    public function exportEmployees(): StreamedResponse
    {
        $employees = Employee::orderBy('name')->get();
        $columns   = ['Code','Name','Email','Department','Position'];

        return new StreamedResponse(function() use ($employees, $columns) {
            // UTF-8 BOM for Excel
            echo "\xEF\xBB\xBF";
            $fp = fopen('php://output','w');
            fputcsv($fp, $columns);

            foreach ($employees as $e) {
                fputcsv($fp, [
                    $e->employee_code,
                    $e->name,
                    $e->email,
                    optional($e->department)->name,
                    optional($e->designation)->name,
                ]);
            }
            fclose($fp);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate',
            'Content-Disposition' => 'attachment; filename="employees.csv"',
        ]);
    }

    /** GET /reports/employees/{employee}/pdf */
    public function downloadEmployeePdf(Employee $employee)
    {
        $pdf = PDF::loadView('reports.pdf.employee_sheet', compact('employee'))
                  ->setPaper('A4', 'portrait');

        return $pdf->stream("employee_{$employee->employee_code}.pdf");
    }

    /** GET /reports/employees/{employee}/cert */
    public function downloadCertificate(Employee $employee)
    {
        $pdf = PDF::loadView('reports.pdf.certificate', compact('employee'))
                  ->setPaper('A4', 'portrait');

        return $pdf->stream("certificate_{$employee->employee_code}.pdf");
    }

    /** GET /reports/attendance (CSV) */
    public function exportAttendance(Request $request): StreamedResponse
    {
        $from    = $request->input('from', Carbon::today()->toDateString());
        $to      = $request->input('to',   Carbon::today()->toDateString());

        $records = Attendance::with('employee')
            ->whereDate('time_in','>=', $from)
            ->whereDate('time_in','<=', $to)
            ->orderBy('time_in')
            ->get();

        $columns = ['Date','Code','Name','Time In','Time Out','Status'];

        return new StreamedResponse(function() use ($records, $columns) {
            echo "\xEF\xBB\xBF";
            $fp = fopen('php://output','w');
            fputcsv($fp, $columns);

            foreach ($records as $att) {
                $status = !$att->time_out ? 'In' : 'Out';
                fputcsv($fp, [
                    optional($att->time_in)->toDateString(),
                    optional($att->employee)->employee_code,
                    optional($att->employee)->name,
                    optional($att->time_in)->format('H:i:s'),
                    optional($att->time_out)->format('H:i:s'),
                    $status,
                ]);
            }
            fclose($fp);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate',
            'Content-Disposition' => 'attachment; filename="attendance.csv"',
        ]);
    }

    /** GET /reports/payroll (CSV) */
    public function exportPayroll(Request $request): StreamedResponse
    {
        $from = Carbon::parse($request->input('from', now()->startOfMonth()->toDateString()))->startOfDay();
        $to   = Carbon::parse($request->input('to',   now()->endOfMonth()->toDateString()))->endOfDay();

        return new StreamedResponse(function() use ($from, $to) {
            echo "\xEF\xBB\xBF";
            $out = fopen('php://output','w');
            fputcsv($out, ['Date','Code','Name','Worked (hr)','Rate/hr','Gross','OT (hr)','OT Pay','Deductions','Net']);

            Attendance::with(['employee.designation','employee.schedule'])
                ->whereBetween('time_in', [$from, $to])
                ->whereNotNull('time_out')
                ->orderBy('time_in')
                ->chunk(200, function($chunk) use ($out) {
                    foreach ($chunk as $att) {
                        $emp  = $att->employee;
                        $rate = (float) ($emp->designation->rate_per_hour ?? 0);

                        $in   = Carbon::parse($att->time_in);
                        $outT = Carbon::parse($att->time_out);
                        $worked = $this->hoursBetween($in, $outT);

                        $sched = 0.0;
                        if ($emp->schedule && $emp->schedule->time_in && $emp->schedule->time_out) {
                            $schIn  = Carbon::parse($emp->schedule->time_in)->setDate($in->year,$in->month,$in->day);
                            $schOut = Carbon::parse($emp->schedule->time_out)->setDate($in->year,$in->month,$in->day);
                            $sched  = $this->hoursBetween($schIn, $schOut);
                        }

                        $ot    = max(0, round($worked - $sched, 2));
                        $gross = round($worked * $rate, 2);
                        $otPay = round($ot * $rate, 2);
                        $net   = round($gross + $otPay, 2);

                        fputcsv($out, [
                            $in->toDateString(),
                            $emp->employee_code,
                            $emp->name,
                            number_format($worked,2),
                            number_format($rate,2),
                            number_format($gross,2),
                            number_format($ot,2),
                            number_format($otPay,2),
                            '0.00',
                            number_format($net,2),
                        ]);
                    }
                });

            fclose($out);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate',
            'Content-Disposition' => 'attachment; filename="payroll.csv"',
        ]);
    }

    /** CSV: /reports/payslips */
    public function exportPayslips(Request $request): StreamedResponse
    {
        $fromStr = $request->input('from', now()->startOfMonth()->toDateString());
        $toStr   = $request->input('to',   now()->endOfMonth()->toDateString());

        $brackets = collect(DB::table('late_deductions')->orderBy('mins_min')->get());

        $employees = Employee::with([
            'designation',
            'schedule',
            'attendances' => fn($q)=> $q
                ->whereBetween('time_in', ["{$fromStr} 00:00:00","{$toStr} 23:59:59"])
                ->orderBy('time_in'),
        ])->orderBy('name')->get();

        $columns = ['Code','Name','From','To','Worked','Rate/hr','Sched','OT','OT Pay','Gross','Deduct','Net'];

        return new StreamedResponse(function() use ($employees, $columns, $fromStr, $toStr, $brackets) {
            echo "\xEF\xBB\xBF";
            $fp = fopen('php://output','w');
            fputcsv($fp, $columns);

            foreach ($employees as $emp) {
                $tw = $ts = $to = 0.0;
                $firstInByDate = [];

                foreach ($emp->attendances as $att) {
                    if (!$att->time_in || !$att->time_out) continue;

                    $in   = Carbon::parse($att->time_in);
                    $outT = Carbon::parse($att->time_out);

                    $dKey = $in->toDateString();
                    if (!isset($firstInByDate[$dKey]) || $in->lt($firstInByDate[$dKey])) {
                        $firstInByDate[$dKey] = $in->copy();
                    }

                    $w = $this->hoursBetween($in, $outT);
                    $tw += $w;

                    if ($emp->schedule && $emp->schedule->time_in && $emp->schedule->time_out) {
                        $schIn  = Carbon::parse($emp->schedule->time_in)->setDate($in->year,$in->month,$in->day);
                        $schOut = Carbon::parse($emp->schedule->time_out)->setDate($in->year,$in->month,$in->day);
                        $s = $this->hoursBetween($schIn, $schOut);
                        $ts += $s;
                        $to += max(0, $w - $s);
                    }
                }

                $rate   = (float) ($emp->designation->rate_per_hour ?? 0);

                // lateness using bracket multipliers (if any)
                $lateDeduct = 0.0;
                if ($emp->schedule && $emp->schedule->time_in) {
                    foreach ($firstInByDate as $date => $firstIn) {
                        $schIn = Carbon::parse($emp->schedule->time_in)
                            ->setDate($firstIn->year, $firstIn->month, $firstIn->day);
                        if ($firstIn->gt($schIn)) {
                            $mins = $schIn->diffInMinutes($firstIn);
                            $br = collect($brackets)->first(function ($b) use ($mins) {
                                return (int)$b->mins_min <= $mins && (int)$b->mins_max >= $mins;
                            });
                            if ($br) {
                                $lateDeduct += round($rate * (float)$br->multiplier, 2);
                            }
                        }
                    }
                }

                $worked = round($tw,2);
                $sched  = round($ts,2);
                $ot     = round($to,2);
                $otPay  = round($ot * $rate, 2);
                $gross  = round($worked * $rate, 2);
                $deduct = round($lateDeduct, 2);
                $net    = round(($gross + $otPay) - $deduct, 2);

                fputcsv($fp, [
                    $emp->employee_code,
                    $emp->name,
                    $fromStr,
                    $toStr,
                    number_format($worked,2),
                    number_format($rate,2),
                    number_format($sched,2),
                    number_format($ot,2),
                    number_format($otPay,2),
                    number_format($gross,2),
                    number_format($deduct,2),
                    number_format($net,2),
                ]);
            }

            fclose($fp);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate',
            'Content-Disposition' => 'attachment; filename="payslips.csv"',
        ]);
    }

    /**
     * HR side: single employee payslip PDF (one slip per page),
     * EXACT page size 4.25in × 11in.
     * GET /reports/payslips/{employee}/pdf?from=YYYY-MM-DD&to=YYYY-MM-DD
     */
    public function payslipEmployeeDownload(Employee $employee, Request $request)
    {
        $period_start = Carbon::parse($request->input('from', now()->startOfMonth()->toDateString()))->startOfDay();
        $period_end   = Carbon::parse($request->input('to',   now()->endOfMonth()->toDateString()))->endOfDay();

        $logs = Attendance::where('employee_id', $employee->id)
            ->whereBetween('time_in', [$period_start, $period_end])
            ->whereNotNull('time_out')
            ->get();

        $workedHours = round($logs->sum(function ($a) {
            $in = Carbon::parse($a->time_in);
            $out = Carbon::parse($a->time_out);
            return $this->hoursBetween($in, $out);
        }), 2);

        $otHours = round($logs->sum(function ($a) {
            $in = Carbon::parse($a->time_in);
            $out = Carbon::parse($a->time_out);
            $hrs = $this->hoursBetween($in, $out);
            // treat hours > 8 per log as OT
            return max(0, floor($hrs) - 8);
        }), 2);

        $ndHours = $logs->sum(function ($a) {
            $in = Carbon::parse($a->time_in);
            $out = Carbon::parse($a->time_out);
            if ($out->lt($in)) $out->addDay();

            $ndStart = $in->copy()->setTime(22,0);
            $ndEnd   = $in->copy()->setTime(6,0)->addDay();
            $startND = $in->gt($ndStart) ? $in : $ndStart;
            $endND   = $out->lt($ndEnd)  ? $out : $ndEnd;

            return $endND->gt($startND) ? intdiv($startND->diffInMinutes($endND), 60) : 0;
        });

        $rate     = (float) (optional($employee->designation)->rate_per_hour ?? 0);
        $basePay  = round($workedHours * $rate, 2);
        $otPay    = round($otHours * $rate * 1.25, 2);
        $ndPay    = round($ndHours * $rate * 0.10, 2);
        $gross    = round($basePay + $otPay + $ndPay, 2);

        $loanMonthly = (float) Loan::where('employee_id', $employee->id)
            ->where('status', 'active')
            ->sum('monthly_amount');

        $loan = $period_start->copy()->day(15)->betweenIncluded($period_start, $period_end)
              ? round($loanMonthly, 2) : 0.0;

        $sss = $phil = $pag = 0.0;
        if ($period_end->isSameDay($period_end->copy()->endOfMonth()) && $gross > 0) {
            $sssBr     = Cache::remember('sss_brackets',     now()->addDay(), fn()=> SssContribution::all());
            $philBr    = Cache::remember('phil_brackets',    now()->addDay(), fn()=> PhilhealthContribution::all());
            $pagibigBr = Cache::remember('pagibig_brackets', now()->addDay(), fn()=> PagibigContribution::all());
            $findBr    = fn($col,$g)=> $col->first(fn($b)=> $b->range_min <= $g && $b->range_max >= $g);

            $sss  = (float) ($findBr($sssBr, $gross)->employee_share ?? 0);
            $phil = round($gross * (($findBr($philBr, $gross)->rate_percent ?? 0)/100) / 2, 2);
            $pag  = (float) ($findBr($pagibigBr, $gross)->employee_share ?? 0);
        }

        $deductions = round($loan + $sss + $phil + $pag, 2);
        $net        = round($gross - $deductions, 2);

        $pdf = PDF::loadView('reports.pdf.payroll', [
            'employee'      => $employee,
            'period_start'  => $period_start,
            'period_end'    => $period_end,
            'rate'          => $rate,
            'worked_hours'  => $workedHours,
            'ot_hours'      => $otHours,
            'base_pay'      => $basePay,
            'ot_pay'        => $otPay,
            'nd_hours'      => $ndHours,
            'nd_pay'        => $ndPay,
            'gross'         => $gross,
            'loan'          => $loan,
            'sss'           => $sss,
            'phil'          => $phil,
            'pag'           => $pag,
            'deductions'    => $deductions,
            'net'           => $net,
        ])->setPaper(self::PAYSHEET_SIZE, 'portrait');

        $filename = sprintf('payslip_%s_%s_%s.pdf',
            $employee->employee_code,
            $period_start->format('Ymd'),
            $period_end->format('Ymd')
        );

        return $pdf->stream($filename);
    }

    /** PAGE: /reports/performance */
    public function performanceIndex(Request $request)
    {
        $from = $request->input('from');
        $to   = $request->input('to');

        $q = PerformanceEvaluation::with(['employee','evaluator'])
            ->orderByDesc('period_start');

        if ($from) $q->whereDate('period_end', '>=', $from);
        if ($to)   $q->whereDate('period_start', '<=', $to);

        $evaluations = $q->get();
        $evalCount   = $evaluations->count();
        $evalAvg     = $evalCount ? round($evaluations->avg('overall_score'), 2) : 0;

        $a = DisciplinaryAction::with(['employee','issuer'])->latest();

        if ($from) {
            $a->where(function ($x) use ($from) {
                $x->where(function ($y) use ($from) {
                    $y->whereNotNull('start_date')->whereDate('start_date', '>=', $from);
                })->orWhere(function ($y) use ($from) {
                    $y->whereNull('start_date')->whereDate('created_at', '>=', $from);
                });
            });
        }
        if ($to) {
            $a->where(function ($x) use ($to) {
                $x->where(function ($y) use ($to) {
                    $y->whereNotNull('end_date')->whereDate('end_date', '<=', $to);
                })->orWhere(function ($y) use ($to) {
                    $y->whereNull('end_date')->whereDate('created_at', '<=', $to);
                });
            });
        }

        $actions        = $a->get();
        $actionsCount   = $actions->count();
        $violationsCnt  = $actions->where('action_type', 'violation')->count();
        $suspensionsCnt = $actions->where('action_type', 'suspension')->count();

        return view('reports.performance', compact(
            'from','to',
            'evaluations','evalCount','evalAvg',
            'actions','actionsCount','violationsCnt','suspensionsCnt'
        ));
    }

    /** CSV: /reports/performance/csv */
    public function exportPerformance(Request $request): StreamedResponse
    {
        $from = $request->input('from');
        $to   = $request->input('to');

        $q = PerformanceEvaluation::with('employee','evaluator')->orderBy('period_start');
        if ($from) $q->whereDate('period_end', '>=', $from);
        if ($to)   $q->whereDate('period_start', '<=', $to);

        $records = $q->get();
        $cols    = ['Code','Name','Period Start','Period End','Overall %','Evaluator','Status','Comments'];

        return new StreamedResponse(function() use ($records, $cols) {
            echo "\xEF\xBB\xBF";
            $fp = fopen('php://output','w');
            fputcsv($fp, $cols);
            foreach($records as $ev){
                fputcsv($fp, [
                    optional($ev->employee)->employee_code,
                    optional($ev->employee)->name,
                    optional($ev->period_start)->toDateString(),
                    optional($ev->period_end)->toDateString(),
                    number_format((float)$ev->overall_score, 2),
                    optional($ev->evaluator)->name,
                    $ev->status,
                    $ev->comments,
                ]);
            }
            fclose($fp);
        },200,[
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate',
            'Content-Disposition' => 'attachment; filename="performance_evaluations.csv"',
        ]);
    }

    /** CSV: /reports/discipline/csv */
    public function exportDiscipline(Request $request): StreamedResponse
    {
        $from = $request->input('from');
        $to   = $request->input('to');

        $a = DisciplinaryAction::with('employee')->latest();

        if ($from) {
            $a->where(function ($x) use ($from) {
                $x->where(function ($y) use ($from) {
                    $y->whereNotNull('start_date')->whereDate('start_date', '>=', $from);
                })->orWhere(function ($y) use ($from) {
                    $y->whereNull('start_date')->whereDate('created_at', '>=', $from);
                });
            });
        }
        if ($to) {
            $a->where(function ($x) use ($to) {
                $x->where(function ($y) use ($to) {
                    $y->whereNotNull('end_date')->whereDate('end_date', '<=', $to);
                })->orWhere(function ($y) use ($to) {
                    $y->whereNull('end_date')->whereDate('created_at', '<=', $to);
                });
            });
        }

        $records = $a->get();
        $cols = ['Date','Code','Name','Type','Category','Severity','Points','Reason','Status','Start','End'];

        return new StreamedResponse(function() use ($records, $cols) {
            echo "\xEF\xBB\xBF";
            $fp = fopen('php://output','w');
            fputcsv($fp, $cols);
            foreach($records as $r){
                $date = $r->start_date ?? $r->created_at;
                fputcsv($fp, [
                    optional($date)->toDateString(),
                    optional($r->employee)->employee_code,
                    optional($r->employee)->name,
                    ucfirst($r->action_type),
                    $r->category,
                    ucfirst($r->severity),
                    $r->points,
                    $r->reason,
                    ucfirst($r->status),
                    optional($r->start_date)->toDateString(),
                    optional($r->end_date)->toDateString(),
                ]);
            }
            fclose($fp);
        },200,[
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate',
            'Content-Disposition' => 'attachment; filename="disciplinary_actions.csv"',
        ]);
    }

    /**
     * Legacy single layout (kept) — locked to 4.25×11
     * GET /reports/payslips/{employee}/pdf-single
     */
    public function employeePayslipPdf(Employee $employee, Request $request)
    {
        $fromStr = $request->input('from', now()->startOfMonth()->toDateString());
        $toStr   = $request->input('to',   now()->endOfMonth()->toDateString());

        $rate = (float) (optional($employee->designation)->rate_per_hour ?? 0);

        $atts = Attendance::where('employee_id', $employee->id)
            ->whereBetween('time_in', ["{$fromStr} 00:00:00", "{$toStr} 23:59:59"])
            ->orderBy('time_in')->get();

        $workedHours = 0.0; $schedHours = 0.0; $otHours = 0.0;

        foreach ($atts as $att) {
            if (!$att->time_in || !$att->time_out) continue;
            $in  = Carbon::parse($att->time_in);
            $out = Carbon::parse($att->time_out);

            $w = $this->hoursBetween($in, $out);
            $workedHours += $w;

            if ($employee->schedule && $employee->schedule->time_in && $employee->schedule->time_out) {
                $schIn  = Carbon::parse($employee->schedule->time_in)->setDate($in->year,$in->month,$in->day);
                $schOut = Carbon::parse($employee->schedule->time_out)->setDate($in->year,$in->month,$in->day);
                $s = $this->hoursBetween($schIn, $schOut);
                $schedHours += $s; 
                $otHours += max(0, $w - $s);
            }
        }

        $basePay = round($workedHours * $rate, 2);
        $otPay   = round($otHours * $rate * 1.25, 2);
        $gross   = round($basePay + $otPay, 2);
        $net     = $gross;

        $pdf = PDF::loadView('reports.pdf.payslip_single', [
            'employee'      => $employee,
            'period_start'  => Carbon::parse($fromStr),
            'period_end'    => Carbon::parse($toStr),
            'rate'          => $rate,
            'worked_hours'  => round($workedHours,2),
            'sched_hours'   => round($schedHours,2),
            'ot_hours'      => round($otHours,2),
            'base_pay'      => $basePay,
            'ot_pay'        => $otPay,
            'gross'         => $gross,
            'deductions'    => 0,
            'net'           => $net,
        ])->setPaper(self::PAYSHEET_SIZE, 'portrait');

        $filename = sprintf('payslip_%s_%s_%s.pdf',
            $employee->employee_code,
            Carbon::parse($fromStr)->format('Ymd'),
            Carbon::parse($toStr)->format('Ymd')
        );

        return $pdf->download($filename);
    }

    /** NEW: Bulk merged payslips (one page per employee) */
    public function bulkPayslipsPdf(Request $request)
    {
        $period_start = Carbon::parse($request->input('from', now()->startOfMonth()->toDateString()))->startOfDay();
        $period_end   = Carbon::parse($request->input('to',   now()->endOfMonth()->toDateString()))->endOfDay();

        // Gov brackets cached
        $sssBr     = Cache::remember('sss_brackets',     now()->addDay(), fn()=> SssContribution::all());
        $philBr    = Cache::remember('phil_brackets',    now()->addDay(), fn()=> PhilhealthContribution::all());
        $pagibigBr = Cache::remember('pagibig_brackets', now()->addDay(), fn()=> PagibigContribution::all());
        $findBr    = fn($col,$g)=> $col->first(fn($b)=> $b->range_min <= $g && $b->range_max >= $g);

        $employees = Employee::with(['department','designation'])->orderBy('name')->get();

        $items = [];

        foreach ($employees as $employee) {
            // Attendance in range
            $logs = Attendance::where('employee_id', $employee->id)
                ->whereBetween('time_in', [$period_start, $period_end])
                ->whereNotNull('time_out')
                ->get();

            $workedHours = round($logs->sum(function ($a) {
                $in = Carbon::parse($a->time_in);
                $out = Carbon::parse($a->time_out);
                return $this->hoursBetween($in, $out);
            }), 2);

            // OT: whole hours beyond 8 per log
            $otHours = round($logs->sum(function ($a) {
                $in = Carbon::parse($a->time_in);
                $out = Carbon::parse($a->time_out);
                $hrs = $this->hoursBetween($in, $out);
                return max(0, floor($hrs) - 8);
            }), 2);

            // Night diff: 22:00–06:00 whole hours
            $ndHours = $logs->sum(function ($a) {
                $in = Carbon::parse($a->time_in);
                $out = Carbon::parse($a->time_out);
                if ($out->lt($in)) $out->addDay();
                $ndStart = $in->copy()->setTime(22,0);
                $ndEnd   = $in->copy()->setTime(6,0)->addDay();
                $startND = $in->gt($ndStart) ? $in : $ndStart;
                $endND   = $out->lt($ndEnd)  ? $out : $ndEnd;
                return $endND->gt($startND) ? intdiv($startND->diffInMinutes($endND), 60) : 0;
            });

            $rate     = (float) (optional($employee->designation)->rate_per_hour ?? 0);
            $basePay  = round($workedHours * $rate, 2);
            $otPay    = round($otHours * $rate * 1.25, 2);
            $ndPay    = round($ndHours * $rate * 0.10, 2);
            $gross    = round($basePay + $otPay + $ndPay, 2);

            // Loan: mid-month
            $loanMonthly = (float) Loan::where('employee_id', $employee->id)
                ->where('status', 'active')
                ->sum('monthly_amount');

            $loan = $period_start->copy()->day(15)->betweenIncluded($period_start, $period_end)
                  ? round($loanMonthly, 2) : 0.0;

            // Gov deductions: EOM only
            $sss = $phil = $pag = 0.0;
            if ($period_end->isSameDay($period_end->copy()->endOfMonth()) && $gross > 0) {
                $sss  = (float) ($findBr($sssBr, $gross)->employee_share ?? 0);
                $phil = round($gross * (($findBr($philBr, $gross)->rate_percent ?? 0)/100) / 2, 2);
                $pag  = (float) ($findBr($pagibigBr, $gross)->employee_share ?? 0);
            }

            $deductions = round($loan + $sss + $phil + $pag, 2);
            $net        = round($gross - $deductions, 2);

            $items[] = [
                'employee'      => $employee,
                'period_start'  => $period_start,
                'period_end'    => $period_end,
                'rate'          => $rate,
                'worked_hours'  => $workedHours,
                'ot_hours'      => $otHours,
                'nd_hours'      => $ndHours,
                'base_pay'      => $basePay,
                'ot_pay'        => $otPay,
                'nd_pay'        => $ndPay,
                'gross'         => $gross,
                'loan'          => $loan,
                'sss'           => $sss,
                'phil'          => $phil,
                'pag'           => $pag,
                'deductions'    => $deductions,
                'net'           => $net,
            ];
        }

        $pdf = PDF::loadView('reports.pdf.payroll_bulk', [
            'items' => $items,
        ])->setPaper(self::PAYSHEET_SIZE, 'portrait');

        $filename = sprintf('Payslips_All_%s_%s.pdf', $period_start->format('Ymd'), $period_end->format('Ymd'));
        if (ob_get_length()) { @ob_end_clean(); }
        return $pdf->stream($filename);
    }
}
