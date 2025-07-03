<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use PDF;

class PayrollController extends Controller
{
    /**
     * Display the payroll report.
     */
    public function index(Request $request)
    {
        $search    = $request->input('search');
        $startDate = $request->filled('start_date')
                     ? Carbon::parse($request->input('start_date'))->toDateString()
                     : Carbon::today()->startOfMonth()->toDateString();
        $endDate   = $request->filled('end_date')
                     ? Carbon::parse($request->input('end_date'))->toDateString()
                     : Carbon::today()->endOfMonth()->toDateString();

        // only active employees, search code or name
        $employees = Employee::where('status','active')
            ->when($search, fn($q,$s)=>
                $q->where('employee_code','like',"%{$s}%")
                  ->orWhere('name','like',"%{$s}%")
            )
            ->with(['designation','schedule'])
            ->orderBy('name')
            ->get();

        $period = CarbonPeriod::create($startDate,$endDate);

        // fetch attendance
        $attendanceIndex = Attendance::whereIn('employee_id',$employees->pluck('id'))
            ->whereDate('time_in','>=',$startDate)
            ->whereDate('time_in','<=',$endDate)
            ->get()
            ->groupBy('employee_id')
            ->map(fn($g)=>
                $g->groupBy(fn($r)=>$r->time_in->toDateString())
            );

        // cache brackets
        $sssBrackets     = Cache::remember('sss_brackets', now()->addDay(), fn()=> \App\Models\SssContribution::all());
        $philBrackets    = Cache::remember('phil_brackets', now()->addDay(), fn()=> \App\Models\PhilhealthContribution::all());
        $pagibigBrackets = Cache::remember('pagibig_brackets', now()->addDay(), fn()=> \App\Models\PagibigContribution::all());

        $findBracket = fn($col,$g)=>
            $col->first(fn($b)=> $b->range_min <= $g && $b->range_max >= $g);

        $rows = [];
        foreach ($period as $day) {
            $date = $day->toDateString();

            foreach ($employees as $emp) {
                $atts = $attendanceIndex->get($emp->id, collect())->get($date, collect());
                $totalSec = 0; $overtimeSec = 0;

                foreach ($atts as $att) {
                    if (! $att->time_out) continue;
                    $in  = Carbon::parse($att->time_in);
                    $out = Carbon::parse($att->time_out);
                    if ($out->lt($in)) $out->addDay();
                    $worked = $in->diffInSeconds($out);
                    $totalSec += $worked;

                    // overtime beyond schedule
                    if ($emp->schedule?->time_in && $emp->schedule?->time_out) {
                        $sIn  = Carbon::parse($emp->schedule->time_in)->setDate($in->year,$in->month,$in->day);
                        $sOut = Carbon::parse($emp->schedule->time_out)->setDate($in->year,$in->month,$in->day);
                        if ($sOut->lt($sIn)) $sOut->addDay();
                        $schedSec = $sIn->diffInSeconds($sOut);
                        if ($worked > $schedSec) $overtimeSec += ($worked - $schedSec);
                    }
                }

                $workedH   = round($totalSec/3600,2);
                $overtimeH = round($overtimeSec/3600,2);
                $rph       = $emp->designation->rate_per_hour ?? 0;
                $grossPay  = round($rph * $workedH,2);
                $otPay     = round($rph * $overtimeH,2);

                // SSS
                $sssRow = $findBracket($sssBrackets,$grossPay);
                $sssEmp = $sssRow->employee_share ?? 0;
                $sssEr  = $sssRow->employer_share ?? 0;

                // PhilHealth (split 50/50)
                $philRow  = $findBracket($philBrackets,$grossPay);
                $prate    = $philRow->rate_percent ?? 0;
                $ptotal   = $grossPay * ($prate/100);
                $philEmp  = round($ptotal/2,2);
                $philEr   = round($ptotal/2,2);

                // Pag-IBIG
                $pagRow  = $findBracket($pagibigBrackets,$grossPay);
                $pagEmp  = $pagRow->employee_share ?? 0;
                $pagEr   = $pagRow->employer_share ?? 0;

                $ded = round($sssEmp + $philEmp + $pagEmp,2);
                $net = round($grossPay + $otPay - $ded,2);

                $rows[] = [
                    'employee_code'       => $emp->employee_code,
                    'employee_name'       => $emp->name,
                    'date'                => $date,
                    'rate_hr'             => number_format($rph,2),
                    'worked_hr'           => number_format($workedH,2),
                    'ot_hr'               => number_format($overtimeH,2),
                    'ot_pay'              => number_format($otPay,2),
                    'sss'                 => number_format($sssEmp,2),
                    'philhealth'          => number_format($philEmp,2),
                    'pagibig'             => number_format($pagEmp,2),
                    'deductions'          => number_format($ded,2),
                    'gross_pay'           => number_format($grossPay,2),
                    'net_pay'             => number_format($net,2),
                    'sss_employer'        => number_format($sssEr,2),
                    'philhealth_employer' => number_format($philEr,2),
                    'pagibig_employer'    => number_format($pagEr,2),
                ];
            }
        }

        usort($rows, fn($a,$b)=>
            [$a['date'],$a['employee_code']] <=> [$b['date'],$b['employee_code']]
        );

        $page    = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $slice   = array_slice($rows,($page-1)*$perPage,$perPage,true);

        $paginator = new LengthAwarePaginator(
            $slice,
            count($rows),
            $perPage,
            $page,
            ['path'=>route('payroll.index'),'query'=>$request->query()]
        );

        return view('payroll.index', [
            'rows'=>$paginator,
            'startDate'=>$startDate,
            'endDate'=>$endDate,
            'search'=>$search,
        ]);
    }

    /**
     * Show details for a single employee’s payslip.
     */
    public function show($id, Request $request)
    {
        $startDate = $request->filled('start_date')
                   ? Carbon::parse($request->input('start_date'))->toDateString()
                   : Carbon::today()->startOfMonth()->toDateString();
        $endDate   = $request->filled('end_date')
                   ? Carbon::parse($request->input('end_date'))->toDateString()
                   : Carbon::today()->endOfMonth()->toDateString();

        $emp = Employee::with(['designation','schedule'])->findOrFail($id);

        $period       = CarbonPeriod::create($startDate, $endDate);
        $overallSec   = 0;

        foreach ($period as $day) {
            $date       = $day->toDateString();
            $attendances = Attendance::where('employee_id', $emp->id)
                                     ->whereDate('time_in', $date)
                                     ->get();

            foreach ($attendances as $att) {
                if (!$att->time_in || !$att->time_out) {
                    continue;
                }
                $in  = Carbon::parse($att->time_in);
                $out = Carbon::parse($att->time_out);
                if ($out->lt($in)) {
                    $out->addDay();
                }
                $overallSec += $in->diffInSeconds($out);
            }
        }

        $workedH  = round($overallSec / 3600, 2);
        $rph      = $emp->designation->rate_per_hour ?? 0;
        $grossPay = round($rph * $workedH, 2);

        // reuse cached brackets
        $sssBrackets     = Cache::get('sss_brackets',     SssContribution::all());
        $philBrackets    = Cache::get('phil_brackets',    PhilhealthContribution::all());
        $pagibigBrackets = Cache::get('pagibig_brackets', PagibigContribution::all());
        $findBracket     = fn($col,$g) => $col->first(fn($b)=> $b->range_min <= $g && $b->range_max >= $g);

        $sssRow = $findBracket($sssBrackets, $grossPay);
        $sssEmp = $sssRow->employee_share ?? 0;
        $sssEr  = $sssRow->employer_share  ?? 0;

        $philRow   = $findBracket($philBrackets, $grossPay);
        $philRate  = $philRow->rate_percent ?? 0;
        $philTotal = $grossPay * ($philRate / 100);
        $philEmp   = round($philTotal / 2, 2);
        $philEr    = round($philTotal / 2, 2);

        $pagRow = $findBracket($pagibigBrackets, $grossPay);
        $pagEmp = $pagRow->employee_share ?? 0;
        $pagEr  = $pagRow->employer_share  ?? 0;

        $totalDeduction = round($sssEmp + $philEmp + $pagEmp, 2);
        $netPay         = round($grossPay - $totalDeduction, 2);

        $emp->worked_hours          = $workedH;
        $emp->gross_pay             = $grossPay;
        $emp->sss                   = $sssEmp;
        $emp->philhealth            = $philEmp;
        $emp->pagibig               = $pagEmp;
        $emp->sss_employer          = $sssEr;
        $emp->philhealth_employer   = $philEr;
        $emp->pagibig_employer      = $pagEr;
        $emp->total_deduction       = $totalDeduction;
        $emp->net_pay               = $netPay;

        return view('payroll.show', [
            'employee'   => $emp,
            'start_date' => $startDate,
            'end_date'   => $endDate,
        ]);
    }

    /**
     * Export selected payroll rows as a PDF.
     */
    public function exportPdf(Request $request)
    {
        $response  = $this->index($request);
        $viewData  = $response->getData();
        $rows      = $viewData['rows'];
        $startDate = $viewData['startDate'];
        $endDate   = $viewData['endDate'];

        $pdf = PDF::loadView('payroll.pdf', [
            'rows'       => $rows,
            'startDate'  => $startDate,
            'endDate'    => $endDate,
        ]);

        return $pdf->download('PayrollDetails.pdf');
    }
}
