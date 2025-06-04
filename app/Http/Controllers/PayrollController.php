<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\SssContribution;
use App\Models\PagibigContribution;
use App\Models\PhilhealthContribution;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Pagination\LengthAwarePaginator;
use PDF;

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

        // 2) Fetch employees
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

                // 6) DEDUCTIONS (Employee Shares)
                $sssEmp        = $this->getSssEmployeeShare($grossPay);
                $philhealthEmp = $this->getPhilhealthEmployeeShare($grossPay);
                $pagibigEmp    = $this->getPagibigEmployeeShare($grossPay);

                // 7) CONTRIBUTIONS (Employer Shares)
                $sssEr         = $this->getSssEmployerShare($grossPay);
                $philhealthEr  = $this->getPhilhealthEmployerShare($grossPay);
                $pagibigEr     = $this->getPagibigEmployerShare($grossPay);

                $totalDeduction = round($sssEmp + $philhealthEmp + $pagibigEmp, 2);
                $netPay         = round($grossPay + $otPay - $totalDeduction, 2);

                $rows[] = [
                    'employee_code'       => $emp->employee_code,
                    'employee_name'       => $emp->name,
                    'date'                => $date,
                    'rate_hr'             => number_format($rph, 2),
                    'worked_hr'           => number_format($workedHours,   2),
                    'ot_hr'               => number_format($overtimeHours, 2),
                    'ot_pay'              => number_format($otPay,         2),
                    'sss'                 => number_format($sssEmp,        2),
                    'philhealth'          => number_format($philhealthEmp, 2),
                    'pagibig'             => number_format($pagibigEmp,    2),
                    'deductions'          => number_format($totalDeduction,2),
                    'gross_pay'           => number_format($grossPay,      2),
                    'net_pay'             => number_format($netPay,        2),
                    'sss_employer'        => number_format($sssEr,         2),
                    'philhealth_employer' => number_format($philhealthEr,  2),
                    'pagibig_employer'    => number_format($pagibigEr,     2),
                ];
            }
        }

        // 8) Sort by date asc, then employee code
        usort($rows, fn($a, $b) =>
            [$a['date'], $a['employee_code']] <=> [$b['date'], $b['employee_code']]
        );

        // 9) Paginate
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

        // 10) Return view
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
        // date parsing
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
        $overallSeconds = 0;
        foreach ($period as $day) {
            $date = $day->toDateString();
            $attendances = Attendance::where('employee_id',$emp->id)
                                     ->whereDate('time_in',$date)
                                     ->get();

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

                $overallSeconds += $workedSec;
            }
        }

        $workedHours = round($overallSeconds / 3600, 2);
        $rph         = $emp->designation->rate_per_hour ?? 0;
        $grossPay    = round($rph * $workedHours, 2);

        // calculate contributions
        $sssEmp        = $this->getSssEmployeeShare($grossPay);
        $philhealthEmp = $this->getPhilhealthEmployeeShare($grossPay);
        $pagibigEmp    = $this->getPagibigEmployeeShare($grossPay);

        $sssEr         = $this->getSssEmployerShare($grossPay);
        $philhealthEr  = $this->getPhilhealthEmployerShare($grossPay);
        $pagibigEr     = $this->getPagibigEmployerShare($grossPay);

        $totalDeduction = round($sssEmp + $philhealthEmp + $pagibigEmp, 2);
        $netPay         = round($grossPay - $totalDeduction, 2);

        // Attach computed values to the model so Blade can use them
        $emp->total_minutes       = round($overallSeconds / 60, 2);
        $emp->gross_pay           = $grossPay;
        $emp->sss                 = $sssEmp;
        $emp->philhealth          = $philhealthEmp;
        $emp->pagibig             = $pagibigEmp;
        $emp->sss_employer        = $sssEr;
        $emp->philhealth_employer = $philhealthEr;
        $emp->pagibig_employer    = $pagibigEr;
        $emp->total_deduction     = $totalDeduction;
        $emp->net_pay             = $netPay;
        $emp->cash_advance        = $emp->cash_advance ?? 0; // if you store cash advance

        return view('payroll.show', [
            'employee'   => $emp,
            'start_date' => $startDate,
            'end_date'   => $endDate,
        ]);
    }

    /**
     * Export selected payroll rows as PDF.
     */
    public function exportPdf(Request $request)
    {
        $response = $this->index($request);
        $viewData = $response->getData();

        $rows      = $viewData['rows'];
        $startDate = $viewData['startDate'];
        $endDate   = $viewData['endDate'];

        $pdf = PDF::loadView('payroll.pdf', [
            'rows'      => $rows,
            'startDate' => $startDate,
            'endDate'   => $endDate,
        ]);

        return $pdf->download('PayrollDetails.pdf');
    }

    // ────────────────────────────────────────────────────────────────────────────
    // ► CONTRIBUTION LOOKUPS (SSS, Pag-IBIG, PhilHealth)
    // ────────────────────────────────────────────────────────────────────────────

    private function getSssEmployeeShare($gross)
    {
        return SssContribution::where('range_min', '<=', $gross)
                              ->where('range_max', '>=', $gross)
                              ->value('employee_share') ?? 250;
    }

    private function getSssEmployerShare($gross)
    {
        return SssContribution::where('range_min', '<=', $gross)
                              ->where('range_max', '>=', $gross)
                              ->value('employer_share') ?? 0;
    }

    private function getPagibigEmployeeShare($gross)
    {
        return PagibigContribution::where('range_min', '<=', $gross)
                                  ->where('range_max', '>=', $gross)
                                  ->value('employee_share') ?? 100;
    }

    private function getPagibigEmployerShare($gross)
    {
        return PagibigContribution::where('range_min', '<=', $gross)
                                  ->where('range_max', '>=', $gross)
                                  ->value('employer_share') ?? 0;
    }

    private function getPhilhealthEmployeeShare($gross)
    {
        $rate = PhilhealthContribution::where('range_min', '<=', $gross)
                                      ->where('range_max', '>=', $gross)
                                      ->value('rate_percent') ?? 4.5;

        // 50% employee share
        return round(($gross * ($rate / 100)) / 2, 2);
    }

    private function getPhilhealthEmployerShare($gross)
    {
        $rate = PhilhealthContribution::where('range_min', '<=', $gross)
                                      ->where('range_max', '>=', $gross)
                                      ->value('rate_percent') ?? 4.5;

        // 50% employer share
        return round(($gross * ($rate / 100)) / 2, 2);
    }
}
