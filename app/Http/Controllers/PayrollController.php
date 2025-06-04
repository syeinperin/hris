<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Pagination\LengthAwarePaginator;
use PDF; // Barryvdh\DomPDF\Facade\Pdf

class PayrollController extends Controller
{
    /**
     * Display the payroll report, one row per employee per day.
     */
    public function index(Request $request)
    {
        // 1) Parse filters
        $search     = $request->input('search');
        $startDate  = $request->filled('start_date')
                      ? Carbon::parse($request->input('start_date'))->toDateString()
                      : Carbon::today()->startOfMonth()->toDateString();
        $endDate    = $request->filled('end_date')
                      ? Carbon::parse($request->input('end_date'))->toDateString()
                      : Carbon::today()->endOfMonth()->toDateString();

        // 2) Fetch employees matching search
        $employees = Employee::when($search, fn($q) =>
                            $q->where('name','like',"%{$search}%")
                              ->orWhere('employee_code','like',"%{$search}%")
                        )
                        ->with('designation','schedule')
                        ->orderBy('name')
                        ->get();

        // 3) Build date period
        $period = CarbonPeriod::create($startDate, $endDate);

        // 4) Assemble rows
        $rows = [];
        foreach ($period as $day) {
            $date = $day->toDateString();

            foreach ($employees as $emp) {
                // pull attendances for that day
                $attendances = Attendance::where('employee_id', $emp->id)
                                         ->whereDate('time_in', $date)
                                         ->get();

                $totalSec    = 0;
                $overtimeSec = 0;

                foreach ($attendances as $att) {
                    if (! $att->time_in || ! $att->time_out) {
                        continue;
                    }
                    $in  = Carbon::parse($att->time_in);
                    $out = Carbon::parse($att->time_out);
                    if ($out->lt($in)) {
                        $out->addDay();
                    }

                    $workedSec = $in->diffInSeconds($out);
                    $totalSec += $workedSec;

                    // calculate overtime beyond scheduled shift
                    if ($emp->schedule?->time_in && $emp->schedule?->time_out) {
                        $schedIn  = Carbon::parse($emp->schedule->time_in)
                                         ->setDate($in->year, $in->month, $in->day);
                        $schedOut = Carbon::parse($emp->schedule->time_out)
                                         ->setDate($in->year, $in->month, $in->day);
                        if ($schedOut->lt($schedIn)) {
                            $schedOut->addDay();
                        }
                        $scheduledSec = $schedIn->diffInSeconds($schedOut);
                        if ($workedSec > $scheduledSec) {
                            $overtimeSec += ($workedSec - $scheduledSec);
                        }
                    }
                }

                // 5) Convert to hours & compute pay
                $rph           = $emp->designation->rate_per_hour ?? 0;
                $workedHours   = round($totalSec    / 3600, 2);
                $overtimeHours = round($overtimeSec / 3600, 2);
                $grossPay      = round($rph * $workedHours,   2);
                $otPay         = round($rph * $overtimeHours, 2);

                // no per-day deductions in this example
                $deduction     = 0;
                $netPay        = round($grossPay + $otPay - $deduction, 2);

                $rows[] = [
                    'employee_code' => $emp->employee_code,
                    'employee_name' => $emp->name,
                    'date'          => $date,
                    'rate_hr'       => number_format($rph, 2),
                    'worked_hr'     => number_format($workedHours,   2),
                    'ot_hr'         => number_format($overtimeHours, 2),
                    'ot_pay'        => number_format($otPay,         2),
                    'deductions'    => number_format($deduction,     2),
                    'gross_pay'     => number_format($grossPay,      2),
                    'net_pay'       => number_format($netPay,        2),
                ];
            }
        }

        // 6) Sort by date asc, then employee code
        usort($rows, fn($a, $b) =>
            [$a['date'], $a['employee_code']] <=> [$b['date'], $b['employee_code']]
        );

        // 7) Paginate
        $page     = LengthAwarePaginator::resolveCurrentPage();
        $perPage  = 10;
        $slice    = array_slice($rows, ($page - 1) * $perPage, $perPage, true);
        $paginator = new LengthAwarePaginator(
            $slice,
            count($rows),
            $perPage,
            $page,
            [
              'path'  => route('payroll.index'),
              'query' => $request->query(),
            ]
        );

        // 8) Return view
        return view('payroll.index', [
            'rows'      => $paginator,
            'startDate' => $startDate,
            'endDate'   => $endDate,
            'search'    => $search,
        ]);
    }

    /**
     * Show details for a single employee’s payslip.
     */
    public function show($id, Request $request)
    {
        // identical date parsing
        $startDate = $request->filled('start_date')
                   ? Carbon::parse($request->input('start_date'))->toDateString()
                   : Carbon::today()->startOfMonth()->toDateString();
        $endDate   = $request->filled('end_date')
                   ? Carbon::parse($request->input('end_date'))->toDateString()
                   : Carbon::today()->endOfMonth()->toDateString();

        $emp = Employee::with(['designation','schedule'])
                       ->findOrFail($id);

        // collect each day’s details
        $period  = CarbonPeriod::create($startDate, $endDate);
        $details = [];

        foreach ($period as $day) {
            $date         = $day->toDateString();
            $att          = Attendance::where('employee_id',$emp->id)
                                      ->whereDate('time_in',$date)
                                      ->first();
            if (! $att || ! $att->time_in || ! $att->time_out) {
                $details[] = [
                    'date'           => $date,
                    'worked_hours'   => 0,
                    'overtime_hours' => 0,
                ];
                continue;
            }

            $in   = Carbon::parse($att->time_in);
            $out  = Carbon::parse($att->time_out);
            if ($out->lt($in)) {
                $out->addDay();
            }
            $workedSec = $in->diffInSeconds($out);

            $overtimeSec = 0;
            if ($emp->schedule?->time_in && $emp->schedule?->time_out) {
                $schedIn  = Carbon::parse($emp->schedule->time_in)
                                 ->setDate($in->year,$in->month,$in->day);
                $schedOut = Carbon::parse($emp->schedule->time_out)
                                 ->setDate($in->year,$in->month,$in->day);
                if ($schedOut->lt($schedIn)) {
                    $schedOut->addDay();
                }
                $scheduledSec = $schedIn->diffInSeconds($schedOut);
                if ($workedSec > $scheduledSec) {
                    $overtimeSec = $workedSec - $scheduledSec;
                }
            }

            $details[] = [
                'date'           => $date,
                'worked_hours'   => round($workedSec / 3600, 2),
                'overtime_hours' => round($overtimeSec / 3600, 2),
            ];
        }

        return view('payroll.show', compact(
            'emp','details','startDate','endDate'
        ));
    }

    /**
     * Export selected payroll rows as PDF.
     */
    public function exportPdf(Request $request)
    {
        // reuse index logic up to $rows...
        $response = $this->index($request);
        $viewData = $response->getData();

        $pdf = PDF::loadView('payroll.pdf', [
            'rows'      => $viewData['rows'],
            'startDate' => $viewData['startDate'],
            'endDate'   => $viewData['endDate'],
        ]);

        return $pdf->download('PayrollDetails.pdf');
    }
}
