<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\LateDeduction;        // ← use LateDeduction instead of Deduction
use App\Models\SssContribution;
use App\Models\PhilhealthContribution;
use App\Models\PagibigContribution;
use App\Models\Loan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use PDF;

class PayrollController extends Controller
{
    /**
     * 1) Interactive Calendar view,
     *    with attendance, manual overrides, leaves, holidays & rest days.
     */
    public function calendar(Request $request)
    {
        $search = $request->input('search', '');
        $month  = $request->input('month', Carbon::now()->format('Y-m'));
        $start  = Carbon::parse("$month-01")->startOfMonth();
        $end    = (clone $start)->endOfMonth();

        $employees = Employee::where('status','active')
            ->when($search, fn($q,$s) =>
                $q->where('employee_code','like',"%{$s}%")
                  ->orWhere('name','like',"%{$s}%")
            )
            ->with('schedule')
            ->orderBy('name')
            ->get();

        $attendance = Attendance::whereIn('employee_id', $employees->pluck('id'))
            ->whereBetween('time_in', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy('employee_id')
            ->map(fn($g) => $g->groupBy(fn($r) => $r->time_in->toDateString()));

        return view('payroll.calendar', compact('employees','attendance','start','end','search','month'));
    }

    /**
     * 2) Paginated payroll report: one row per employee for a single day,
     *    plus inline Loans column and late-deduction.
     */
    public function index(Request $request)
    {
        $date   = $request->input('date', Carbon::today()->toDateString());
        $search = $request->input('search', '');
        $day    = Carbon::parse($date)->day;

        // 1) Fetch employees
        $employees = Employee::where('status','active')
            ->when($search, fn($q,$s)=>
                $q->where('employee_code','like',"%{$s}%")
                  ->orWhere('name','like',"%{$s}%")
            )
            ->with(['designation','schedule'])
            ->orderBy('name')
            ->get();

        // 2) Active loan counts
        $loanCounts = Loan::whereIn('employee_id',$employees->pluck('id'))
            ->where('status','active')
            ->groupBy('employee_id')
            ->pluck(DB::raw('count(*)'),'employee_id');

        // 3) Gov’t contribution brackets
        $sssBr     = Cache::remember('sss_brackets', now()->addDay(),   fn()=> SssContribution::all());
        $philBr    = Cache::remember('phil_brackets', now()->addDay(), fn()=> PhilhealthContribution::all());
        $pagibigBr = Cache::remember('pagibig_brackets', now()->addDay(), fn()=> PagibigContribution::all());
        $findBr    = fn($col,$gross)=>
            $col->first(fn($b)=> $b->range_min <= $gross && $b->range_max >= $gross);

        $rows = [];
        foreach ($employees as $emp) {
            $rateHr = $emp->designation->rate_per_hour ?? 0;

            // attendance
            $att = Attendance::where('employee_id',$emp->id)
                             ->whereDate('time_in',$date)
                             ->first();

            $hrs = $otHrs = $gross = $otPay = 0;
            if ($att && $att->time_out) {
                $in   = Carbon::parse($att->time_in);
                $out  = Carbon::parse($att->time_out);
                if ($out->lt($in)) $out->addDay();
                $sec  = $in->diffInSeconds($out);
                $hrs  = round($sec/3600,2);

                if ($sch = $emp->schedule) {
                    $sIn  = Carbon::parse($sch->time_in)->setDate($in->year,$in->month,$in->day);
                    $sOut = Carbon::parse($sch->time_out)->setDate($in->year,$in->month,$in->day);
                    if ($sOut->lt($sIn)) $sOut->addDay();
                    $otHrs = max(0, round(($sec - $sIn->diffInSeconds($sOut))/3600,2));
                }

                $gross = round($hrs * $rateHr,2);
                $otPay = round($otHrs * $rateHr,2);
            }

            // gov’t on last day only
            $endOfMo = Carbon::parse($date)->endOfMonth();
            $isLast  = Carbon::parse($date)->isSameDay($endOfMo);
            $sssEmp  = $isLast ? ($findBr($sssBr,$gross)->employee_share ?? 0) : 0;
            $philEmp = $isLast
                ? round($gross * (($findBr($philBr,$gross)->rate_percent ?? 0)/100)/2,2)
                : 0;
            $pagEmp  = $isLast ? ($findBr($pagibigBr,$gross)->employee_share ?? 0) : 0;

            // late-deduction bracket
            $lateDeduction = 0;
            if ($att && $emp->schedule) {
                $actualIn = Carbon::parse($att->time_in);
                $schedIn  = Carbon::parse($emp->schedule->time_in)
                                    ->setDate($actualIn->year,$actualIn->month,$actualIn->day);
                if ($actualIn->gt($schedIn)) {
                    $mins = $schedIn->diffInMinutes($actualIn);
                    $br   = LateDeduction::where('mins_min','<=',$mins)
                                         ->where('mins_max','>=',$mins)
                                         ->first();
                    if ($br) {
                        $lateDeduction = round($rateHr * $br->multiplier,2);
                    }
                }
            }

            // always include loan deduction
            $loanDed = $loanCounts->get($emp->id,0) * $rateHr;

            $totalDed = round($sssEmp + $philEmp + $pagEmp + $lateDeduction + $loanDed,2);
            $netPay   = round($gross + $otPay - $totalDed,2);

            $rows[] = [
                'employee_id'    => $emp->id,
                'employee_code'  => $emp->employee_code,
                'employee_name'  => $emp->name,
                'rate_hr'        => number_format($rateHr,2),
                'worked_hr'      => number_format($hrs,2),
                'ot_hr'          => number_format($otHrs,2),
                'ot_pay'         => number_format($otPay,2),
                'sss'            => number_format($sssEmp,2),
                'philhealth'     => number_format($philEmp,2),
                'pagibig'        => number_format($pagEmp,2),
                'late_deduction' => number_format($lateDeduction,2),
                'loan_deduction'=> number_format($loanDed,2),
                'deductions'     => number_format($totalDed,2),
                'gross_pay'      => number_format($gross,2),
                'net_pay'        => number_format($netPay,2),
            ];
        }

        // paginate
        $page    = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $slice   = array_slice($rows, ($page-1)*$perPage, $perPage);
        $paginator = new LengthAwarePaginator(
            $slice, count($rows), $perPage, $page,
            ['path'=>route('payroll.index'),'query'=>$request->query()]
        );

        // inline loans
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
     * 3) Show one employee’s payslip,
     *    with per-day late deduction, loan & gov’t contributions.
     */
    public function show($id, Request $request)
    {
        $month        = $request->input('month', Carbon::today()->format('Y-m'));
        $startOfMonth = Carbon::parse("$month-01")->startOfMonth();
        $endOfMonth   = (clone $startOfMonth)->endOfMonth();

        $employee = Employee::with(['designation','schedule'])->findOrFail($id);

        // gov’t brackets
        $sssBr     = Cache::get('sss_brackets');
        $philBr    = Cache::get('phil_brackets');
        $pagibigBr = Cache::get('pagibig_brackets');
        $findBr    = fn($col,$g)=>
            $col->first(fn($b)=> $b->range_min <= $g && $b->range_max >= $g);

        // monthly loan
        $monthlyLoanDed = Loan::where('employee_id',$id)
                              ->where('status','active')
                              ->sum('monthly_amount');

        $firstRows  = [];
        $secondRows = [];

        for ($day = $startOfMonth->copy(); $day->lte($endOfMonth); $day->addDay()) {
            $atts     = Attendance::where('employee_id',$id)
                                  ->whereDate('time_in',$day->toDateString())
                                  ->get();
            $totalSec = $otSec = 0;
            foreach ($atts as $att) {
                if (! $att->time_out) continue;
                $in   = Carbon::parse($att->time_in);
                $out  = Carbon::parse($att->time_out);
                if ($out->lt($in)) $out->addDay();
                $sec      = $in->diffInSeconds($out);
                $totalSec += $sec;

                if ($employee->schedule) {
                    $sIn  = Carbon::parse($employee->schedule->time_in)
                               ->setDate($in->year,$in->month,$in->day);
                    $sOut = Carbon::parse($employee->schedule->time_out)
                               ->setDate($in->year,$in->month,$in->day);
                    if ($sOut->lt($sIn)) $sOut->addDay();
                    if ($sec > $sIn->diffInSeconds($sOut)) {
                        $otSec += $sec - $sIn->diffInSeconds($sOut);
                    }
                }
            }

            $hrs   = round($totalSec/3600,2);
            $otHrs = round($otSec/3600,2);
            $rph   = $employee->designation->rate_per_hour ?? 0;
            $gross = round($hrs * $rph,2);
            $otPay = round($otHrs * $rph,2);

            // gov’t last day only
            if ($day->isSameDay($endOfMonth)) {
                $sssEmp  = $findBr($sssBr,$gross)->employee_share  ?? 0;
                $philEmp = round(
                    $gross * (($findBr($philBr,$gross)->rate_percent ?? 0)/100)/2,
                    2
                );
                $pagEmp  = $findBr($pagibigBr,$gross)->employee_share ?? 0;
            } else {
                $sssEmp = $philEmp = $pagEmp = 0;
            }

            // late-deduction
            $lateDeduction = 0;
            if ($employee->schedule && $atts->first()?->time_in) {
                $firstIn  = Carbon::parse($atts->first()->time_in);
                $schedIn  = Carbon::parse($employee->schedule->time_in)
                                 ->setDate($firstIn->year,$firstIn->month,$firstIn->day);
                if ($firstIn->gt($schedIn)) {
                    $mins = $schedIn->diffInMinutes($firstIn);
                    $br   = LateDeduction::where('mins_min','<=',$mins)
                                         ->where('mins_max','>=',$mins)
                                         ->first();
                    if ($br) {
                        $lateDeduction = round($rph * $br->multiplier,2);
                    }
                }
            }

            // loan on 15th only
            $loanDed = $day->day === 15 ? $monthlyLoanDed : 0;

            $totalDed = round($sssEmp + $philEmp + $pagEmp + $lateDeduction + $loanDed,2);
            $net      = round($gross + $otPay - $totalDed,2);

            $row = [
                'date'        => $day->toDateString(),
                'worked_hr'   => number_format($hrs,2),
                'ot_hr'       => number_format($otHrs,2),
                'gross'       => number_format($gross,2),
                'sss'         => number_format($sssEmp,2),
                'philhealth'  => number_format($philEmp,2),
                'pagibig'     => number_format($pagEmp,2),
                'deductions'  => number_format($totalDed,2),
                'loan'        => number_format($loanDed,2),
                'late'        => number_format($lateDeduction,2),
                'net'         => number_format($net,2),
            ];

            if ($day->day <= 15) {
                $firstRows[]  = $row;
            } else {
                $secondRows[] = $row;
            }
        }

        // summary
        $sum = fn($set,$key) =>
            array_sum(array_map(fn($r)=> floatval(str_replace(',','',$r[$key])),$set));

        $summary = [
            'first'  => [
                'gross'      => $sum($firstRows,'gross'),
                'deductions' => $sum($firstRows,'deductions'),
                'loan'       => $sum($firstRows,'loan'),
                'late'       => $sum($firstRows,'late'),
                'net'        => $sum($firstRows,'net'),
            ],
            'second' => [
                'gross'      => $sum($secondRows,'gross'),
                'deductions' => $sum($secondRows,'deductions'),
                'net'        => $sum($secondRows,'net'),
            ],
        ];

        // active loans footer
        $loans = Loan::with(['loanType','plan'])
            ->where('employee_id',$id)
            ->where('status','active')
            ->orderBy('released_at','desc')
            ->get();

        return view('payroll.show', compact(
            'employee','month','firstRows','secondRows','summary','loans'
        ));
    }

    /**
     * 4) Export the payroll report as PDF.
     */
    public function exportPdf(Request $request)
    {
        $response  = $this->index($request);
        $viewData  = $response->getData();
        $rows      = $viewData['rows'];
        // no startDate/endDate in index() so default null
        $startDate = $viewData['startDate'] ?? null;
        $endDate   = $viewData['endDate']   ?? null;

        $pdf = PDF::loadView('payroll.pdf', compact('rows','startDate','endDate'));
        return $pdf->download('PayrollDetails.pdf');
    }

    /**
     * 5) AJAX: toggle manual ↔ auto attendance on calendar.
     */
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
}
