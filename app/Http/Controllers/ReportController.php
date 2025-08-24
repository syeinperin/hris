<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB; // use DB for late_deductions
use Carbon\Carbon;

// Models
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\PerformanceEvaluation;
use App\Models\DisciplinaryAction;

// PDF
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class ReportController extends Controller
{
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

    /** GET /reports/attendance */
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
            $fp = fopen('php://output','w');
            fputcsv($fp, $columns);
            foreach ($records as $att) {
                $status = !$att->time_in
                    ? 'Absent'
                    : (!$att->time_out ? 'In' : 'Out');

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
            'Content-Type'=>'text/csv; charset=UTF-8',
            'Content-Disposition'=>'attachment; filename="attendance.csv"',
        ]);
    }

    /** GET /reports/payroll */
    public function exportPayroll(Request $request): StreamedResponse
    {
        $from = Carbon::parse($request->input('from', now()->startOfMonth()->toDateString()))->startOfDay();
        $to   = Carbon::parse($request->input('to',   now()->endOfMonth()->toDateString()))->endOfDay();

        return new StreamedResponse(function() use ($from, $to) {
            $out = fopen('php://output','w');
            fputcsv($out, ['Date','Code','Name','Worked (hr)','Rate/hr','Gross','OT (hr)','OT Pay','Deductions','Net']);

            Attendance::with(['employee.designation','employee.schedule'])
                ->whereBetween('time_in', [$from, $to])
                ->whereNotNull('time_out')
                ->orderBy('time_in')
                ->chunk(200, function($chunk) use ($out) {
                    foreach ($chunk as $att) {
                        $emp  = $att->employee;
                        $rate = $emp->designation->rate_per_hour ?? 0;
                        $in   = Carbon::parse($att->time_in);
                        $outT = Carbon::parse($att->time_out);
                        if ($outT->lt($in)) $outT->addDay();
                        $worked = round($in->floatDiffInMinutes($outT)/60,2);

                        $sched = 0;
                        if ($emp->schedule && $emp->schedule->time_in && $emp->schedule->time_out) {
                            $schIn  = Carbon::parse($emp->schedule->time_in)->setDate($in->year,$in->month,$in->day);
                            $schOut = Carbon::parse($emp->schedule->time_out)->setDate($in->year,$in->month,$in->day);
                            if ($schOut->lt($schIn)) $schOut->addDay();
                            $sched = round($schIn->floatDiffInMinutes($schOut)/60,2);
                        }

                        $ot    = max(0, round($worked - $sched,2));
                        $gross = round($worked * $rate,2);
                        $otPay = round($ot * $rate,2);
                        $net   = round($gross + $otPay,2);

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
            'Content-Type'=>'text/csv; charset=UTF-8',
            'Content-Disposition'=>'attachment; filename="payroll.csv"',
        ]);
    }

    /**
     * GET /reports/payslips
     * Computes lateness-based deductions from the `late_deductions` table.
     * If the table is empty, it falls back to rounding lateness up to 0.25h steps.
     */
    public function exportPayslips(Request $request): StreamedResponse
    {
        $fromStr = $request->input('from', now()->startOfMonth()->toDateString());
        $toStr   = $request->input('to',   now()->endOfMonth()->toDateString());

        $brackets = collect(DB::table('late_deductions')->orderBy('mins_min')->get());

        $fallbackLateHours = function (int $mins): float {
            if ($mins <= 0) return 0.0;
            $hours = ceil($mins / 15) * 0.25;
            return min($hours, 23.75);
        };

        $employees = Employee::with([
            'designation',
            'schedule',
            'attendances' => fn($q)=> $q
                ->whereBetween('time_in', ["{$fromStr} 00:00:00","{$toStr} 23:59:59"])
                ->orderBy('time_in'),
        ])->orderBy('name')->get();

        $columns = ['Code','Name','From','To','Worked','Rate/hr','Sched','OT','OT Pay','Gross','Deduct','Net'];

        return new StreamedResponse(function() use ($employees, $columns, $fromStr, $toStr, $brackets, $fallbackLateHours) {
            $fp = fopen('php://output','w');
            fputcsv($fp, $columns);

            foreach ($employees as $emp) {
                $tw = $ts = $to = 0;
                $firstInByDate = [];

                foreach ($emp->attendances as $att) {
                    if (!$att->time_in || !$att->time_out) continue;

                    $in   = Carbon::parse($att->time_in);
                    $outT = Carbon::parse($att->time_out);
                    if ($outT->lt($in)) $outT->addDay();

                    $dKey = $in->toDateString();
                    if (!isset($firstInByDate[$dKey]) || $in->lt($firstInByDate[$dKey])) {
                        $firstInByDate[$dKey] = $in->copy();
                    }

                    $w = $in->floatDiffInMinutes($outT)/60;
                    $tw += $w;

                    if ($emp->schedule && $emp->schedule->time_in && $emp->schedule->time_out) {
                        $schIn  = Carbon::parse($emp->schedule->time_in)->setDate($in->year,$in->month,$in->day);
                        $schOut = Carbon::parse($emp->schedule->time_out)->setDate($in->year,$in->month,$in->day);
                        if ($schOut->lt($schIn)) $schOut->addDay();
                        $s = $schIn->floatDiffInMinutes($schOut)/60; $ts += $s;
                        $to += max(0, $w - $s);
                    }
                }

                $rate   = $emp->designation->rate_per_hour ?? 0;
                $lateDeduct = 0.0;

                if ($emp->schedule && $emp->schedule->time_in) {
                    foreach ($firstInByDate as $date => $firstIn) {
                        $schIn = Carbon::parse($emp->schedule->time_in)
                            ->setDate($firstIn->year, $firstIn->month, $firstIn->day);
                        if ($firstIn->gt($schIn)) {
                            $mins = $schIn->diffInMinutes($firstIn);

                            if ($brackets->isNotEmpty()) {
                                $br = $brackets->first(function ($b) use ($mins) {
                                    return (int)$b->mins_min <= $mins && (int)$b->mins_max >= $mins;
                                });
                                $mult = $br ? (float)$br->multiplier : 0.0;
                                $lateDeduct += round($rate * $mult, 2);
                            } else {
                                $lateDeduct += round($rate * $fallbackLateHours($mins), 2);
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
            'Content-Type'=>'text/csv; charset=UTF-8',
            'Content-Disposition'=>'attachment; filename="payslips.csv"',
        ]);
    }

    /** PAGE: /reports/performance — Evaluations + Violations/Suspensions */
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

    /** CSV: /reports/performance/csv — Evaluations */
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
            'Content-Type'=>'text/csv; charset=UTF-8',
            'Content-Disposition'=>'attachment; filename="performance_evaluations.csv"',
        ]);
    }

    /** CSV: /reports/discipline/csv — Violations/Suspensions */
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
            'Content-Type'=>'text/csv; charset=UTF-8',
            'Content-Disposition'=>'attachment; filename="disciplinary_actions.csv"',
        ]);
    }

    /**
     * NEW: Single employee payslip PDF for a date range.
     * Route: GET /reports/payslips/{employee}/pdf?from=YYYY-MM-DD&to=YYYY-MM-DD
     */
    public function employeePayslipPdf(Employee $employee, Request $request)
    {
        $fromStr = $request->input('from', now()->startOfMonth()->toDateString());
        $toStr   = $request->input('to',   now()->endOfMonth()->toDateString());

        $rate = (float) (optional($employee->designation)->rate_per_hour ?? 0);

        // Attendances in range
        $atts = Attendance::where('employee_id', $employee->id)
            ->whereBetween('time_in', ["{$fromStr} 00:00:00", "{$toStr} 23:59:59"])
            ->orderBy('time_in')
            ->get();

        // Late brackets via DB
        $brackets = collect(DB::table('late_deductions')->orderBy('mins_min')->get());
        $fallbackLateHours = function (int $mins): float {
            if ($mins <= 0) return 0.0;
            $hours = ceil($mins / 15) * 0.25; // round up 15m → 0.25h
            return min($hours, 23.75);
        };

        $workedHours = 0.0;
        $schedHours  = 0.0;
        $otHours     = 0.0;
        $lateDeduct  = 0.0;

        // earliest IN per day
        $firstInByDate = [];

        foreach ($atts as $att) {
            if (!$att->time_in || !$att->time_out) continue;

            $in  = Carbon::parse($att->time_in);
            $out = Carbon::parse($att->time_out);
            if ($out->lt($in)) $out->addDay();

            $key = $in->toDateString();
            if (!isset($firstInByDate[$key]) || $in->lt($firstInByDate[$key])) {
                $firstInByDate[$key] = $in->copy();
            }

            $w = $in->floatDiffInMinutes($out) / 60;
            $workedHours += $w;

            if ($employee->schedule && $employee->schedule->time_in && $employee->schedule->time_out) {
                $schIn  = Carbon::parse($employee->schedule->time_in)->setDate($in->year,$in->month,$in->day);
                $schOut = Carbon::parse($employee->schedule->time_out)->setDate($in->year,$in->month,$in->day);
                if ($schOut->lt($schIn)) $schOut->addDay();

                $s = $schIn->floatDiffInMinutes($schOut) / 60;
                $schedHours += $s;
                $otHours    += max(0, $w - $s);
            }
        }

        // Lateness deduction
        if ($employee->schedule && $employee->schedule->time_in) {
            foreach ($firstInByDate as $date => $firstIn) {
                $schIn = Carbon::parse($employee->schedule->time_in)
                    ->setDate($firstIn->year, $firstIn->month, $firstIn->day);

                if ($firstIn->gt($schIn)) {
                    $mins = $schIn->diffInMinutes($firstIn);

                    if ($brackets->isNotEmpty()) {
                        $br = $brackets->first(fn($b) =>
                            (int)$b->mins_min <= $mins && (int)$b->mins_max >= $mins
                        );
                        $mult = $br ? (float)$br->multiplier : 0.0;
                        $lateDeduct += round($rate * $mult, 2);
                    } else {
                        $lateDeduct += round($rate * $fallbackLateHours($mins), 2);
                    }
                }
            }
        }

        // Amounts
        $workedHours = round($workedHours, 2);
        $schedHours  = round($schedHours,  2);
        $otHours     = round($otHours,     2);

        $basePay = round($workedHours * $rate, 2);
        $otPay   = round($otHours * $rate * 1.25, 2); // regular OT
        $gross   = round($basePay + $otPay, 2);
        $deduct  = round($lateDeduct, 2);
        $net     = round($gross - $deduct, 2);

        $pdf = PDF::loadView('reports.pdf.payslip_single', [
            'employee'      => $employee,
            'period_start'  => Carbon::parse($fromStr),
            'period_end'    => Carbon::parse($toStr),
            'rate'          => $rate,
            'worked_hours'  => $workedHours,
            'sched_hours'   => $schedHours,
            'ot_hours'      => $otHours,
            'base_pay'      => $basePay,
            'ot_pay'        => $otPay,
            'gross'         => $gross,
            'deductions'    => $deduct,
            'net'           => $net,
        ])->setPaper('A4','portrait');

        $filename = sprintf('payslip_%s_%s_%s.pdf',
            $employee->employee_code,
            Carbon::parse($fromStr)->format('Ymd'),
            Carbon::parse($toStr)->format('Ymd')
        );

        return $pdf->download($filename);
    }
}
