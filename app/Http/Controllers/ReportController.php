<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\PerformanceEvaluation;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Landing page for reports.
     */
    public function index()
    {
        return view('reports.index');
    }

    /**
     * Export Employee List as CSV.
     */
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
            'Content-Disposition' => 'inline; filename="employees.csv"',
        ]);
    }

    /**
     * Export Attendance as CSV.
     */
    public function exportAttendance(Request $request): StreamedResponse
    {
        $from = $request->input('from', Carbon::today()->toDateString());
        $to   = $request->input('to',   Carbon::today()->toDateString());

        $records = Attendance::with('employee')
            ->whereDate('time_in','>=',$from)
            ->whereDate('time_in','<=',$to)
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
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="attendance.csv"',
        ]);
    }

    /**
     * Export Payroll Summary as CSV, one row per day/attendance.
     * Corrected overtime = worked – scheduled (if positive).
     */
    public function exportPayroll(Request $request): StreamedResponse
    {
        // parse date range
        $from = Carbon::parse(
            $request->input('from', now()->startOfMonth()->toDateString())
        )->startOfDay();
        $to = Carbon::parse(
            $request->input('to', now()->endOfMonth()->toDateString())
        )->endOfDay();

        return new StreamedResponse(function() use ($from, $to) {
            $out = fopen('php://output','w');

            // HEADERS
            fputcsv($out, [
                'Date',
                'Code',
                'Name',
                'Worked (hr)',
                'Rate/hr',
                'Gross Pay',
                'OT (hr)',
                'OT Pay',
                'Deductions',
                'Net Pay',
            ]);

            // Stream each attendance record with a time_out
            Attendance::with(['employee.designation','employee.schedule'])
                ->whereBetween('time_in', [$from, $to])
                ->whereNotNull('time_out')
                ->orderBy('time_in')
                ->chunk(200, function($chunk) use ($out) {
                    foreach ($chunk as $att) {
                        $emp   = $att->employee;
                        $rate  = $emp->designation->rate_per_hour ?? 0;
                        $date  = Carbon::parse($att->time_in)->toDateString();

                        // actual worked hours
                        $in    = Carbon::parse($att->time_in);
                        $outT  = Carbon::parse($att->time_out);
                        if ($outT->lt($in)) {
                            $outT->addDay();
                        }
                        $worked = round($in->floatDiffInMinutes($outT) / 60, 2);

                        // scheduled hours (if schedule exists)
                        $schedHours = 0.0;
                        if ($emp->schedule && $emp->schedule->time_in && $emp->schedule->time_out) {
                            $schIn  = Carbon::parse($emp->schedule->time_in)
                                        ->setDate($in->year, $in->month, $in->day);
                            $schOut = Carbon::parse($emp->schedule->time_out)
                                        ->setDate($in->year, $in->month, $in->day);
                            // if schedule crosses midnight:
                            if ($schOut->lt($schIn)) {
                                $schOut->addDay();
                            }
                            $schedHours = round($schIn->floatDiffInMinutes($schOut) / 60, 2);
                        }

                        // overtime hours = worked – scheduled (min 0)
                        $otHours = max(0, round($worked - $schedHours, 2));
                        $gross   = round($worked * $rate, 2);
                        $otPay   = round($otHours * $rate, 2);

                        // placeholder for deductions
                        $deductions = 0.00;

                        // net pay
                        $net = round(($gross + $otPay) - $deductions, 2);

                        fputcsv($out, [
                            $date,
                            $emp->employee_code,
                            $emp->name,
                            number_format($worked,2),
                            number_format($rate,2),
                            number_format($gross,2),
                            number_format($otHours,2),
                            number_format($otPay,2),
                            number_format($deductions,2),
                            number_format($net,2),
                        ]);
                    }
                });

            fclose($out);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="payroll_by_day_'.now()->format('Ymd_His').'.csv"',
        ]);
    }

    /**
     * Export Payslips as CSV (one row per employee for the whole period).
     */
    public function exportPayslips(Request $request): StreamedResponse
    {
        $fromStr = $request->input('from', now()->startOfMonth()->toDateString());
        $toStr   = $request->input('to',   now()->endOfMonth()->toDateString());
        $from    = Carbon::parse($fromStr)->startOfDay();
        $to      = Carbon::parse($toStr)->endOfDay();

        $employees = Employee::with([
                'designation',
                'schedule',
                'attendances' => fn($q) => $q->whereBetween('time_in', ["{$fromStr} 00:00:00","{$toStr} 23:59:59"]),
                'deductions',
            ])
            ->orderBy('name')
            ->get();

        $columns = [
            'Code',
            'Name',
            'From',
            'To',
            'Worked (hr)',
            'Rate/hr',
            'Scheduled (hr)',
            'OT (hr)',
            'OT Pay',
            'Gross Pay',
            'Deductions',
            'Net Pay',
        ];

        return new StreamedResponse(function() use ($employees, $columns, $fromStr, $toStr, $from, $to) {
            $fp = fopen('php://output','w');
            fputcsv($fp, $columns);

            foreach ($employees as $emp) {
                $totalWorked   = 0.0;
                $totalSched    = 0.0;
                $totalOT       = 0.0;

                foreach ($emp->attendances as $att) {
                    if (! $att->time_in || ! $att->time_out) continue;

                    $in   = Carbon::parse($att->time_in);
                    $outT = Carbon::parse($att->time_out);
                    if ($outT->lt($in)) {
                        $outT->addDay();
                    }
                    $worked = $in->floatDiffInMinutes($outT) / 60;
                    $totalWorked += $worked;

                    if ($emp->schedule && $emp->schedule->time_in && $emp->schedule->time_out) {
                        $schIn  = Carbon::parse($emp->schedule->time_in)
                                    ->setDate($in->year, $in->month, $in->day);
                        $schOut = Carbon::parse($emp->schedule->time_out)
                                    ->setDate($in->year, $in->month, $in->day);
                        if ($schOut->lt($schIn)) {
                            $schOut->addDay();
                        }
                        $schedH = $schIn->floatDiffInMinutes($schOut) / 60;
                        $totalSched += $schedH;

                        $ot = max(0, $worked - $schedH);
                        $totalOT += $ot;
                    }
                }

                $worked    = round($totalWorked,2);
                $sched     = round($totalSched,2);
                $otHours   = round($totalOT,2);
                $rate      = $emp->designation->rate_per_hour ?? 0;
                $otPay     = round($otHours * $rate, 2);
                $gross     = round($worked * $rate, 2);
                $deducts   = $emp->deductions()
                    ->where('effective_from','<=',$toStr)
                    ->where(fn($q)=> $q
                        ->whereNull('effective_until')
                        ->orWhere('effective_until','>=',$fromStr)
                    )
                    ->sum('amount');
                $net       = round(($gross + $otPay) - $deducts, 2);

                fputcsv($fp, [
                    $emp->employee_code,
                    $emp->name,
                    $fromStr,
                    $toStr,
                    number_format($worked,2),
                    number_format($rate,2),
                    number_format($sched,2),
                    number_format($otHours,2),
                    number_format($otPay,2),
                    number_format($gross,2),
                    number_format($deducts,2),
                    number_format($net,2),
                ]);
            }

            fclose($fp);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="payslips.csv"',
        ]);
    }

    /**
     * Export Performance Evaluations as CSV.
     */
    public function exportPerformance(Request $request): StreamedResponse
    {
        $from  = $request->input('from');
        $to    = $request->input('to');
        $query = PerformanceEvaluation::with('employee')->orderBy('evaluation_date');

        if ($from) $query->whereDate('evaluation_date','>=',$from);
        if ($to)   $query->whereDate('evaluation_date','<=',$to);

        $records = $query->get();
        $columns = ['Code','Name','Evaluation Date','Score','Comments'];

        return new StreamedResponse(function() use ($records, $columns) {
            $fp = fopen('php://output','w');
            fputcsv($fp, $columns);

            foreach ($records as $ev) {
                fputcsv($fp, [
                    optional($ev->employee)->employee_code,
                    optional($ev->employee)->name,
                    optional($ev->evaluation_date)->toDateString(),
                    $ev->score,
                    $ev->comments,
                ]);
            }

            fclose($fp);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="performance.csv"',
        ]);
    }
}
