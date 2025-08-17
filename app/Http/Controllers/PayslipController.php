<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Payslip;
use App\Models\Attendance;
use App\Models\Loan;
use App\Models\SssContribution;
use App\Models\PhilhealthContribution;
use App\Models\PagibigContribution;
use Carbon\Carbon;

class PayslipController extends Controller
{
    public function index()
    {
        $payslips = Auth::user()
            ->payslips()
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('payslips.index', compact('payslips'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'period_start' => 'required|date',
            'period_end'   => 'required|date|after_or_equal:period_start',
        ]);

        $employee = Auth::user()->employee;
        if (!$employee) {
            return back()->with('error', 'Complete your employee profile first.');
        }

        $empId       = $employee->id;
        $periodStart = Carbon::parse($data['period_start'])->startOfDay();
        $periodEnd   = Carbon::parse($data['period_end'])->endOfDay();

        // 1) Worked minutes → hours
        $minutes = Attendance::where('employee_id', $empId)
            ->whereBetween('time_in', [$periodStart, $periodEnd])
            ->whereNotNull('time_out')
            ->get()
            ->sum(function ($a) {
                $in  = Carbon::parse($a->time_in);
                $out = Carbon::parse($a->time_out);
                if ($out->lt($in)) $out->addDay();
                return $in->diffInMinutes($out);
            });
        $workedHours = round($minutes / 60, 2);

        // 2) OT (whole hours beyond 8h for each log)
        $otMinutes = Attendance::where('employee_id', $empId)
            ->whereBetween('time_in', [$periodStart, $periodEnd])
            ->whereNotNull('time_out')
            ->get()
            ->sum(function ($a) {
                $in  = Carbon::parse($a->time_in);
                $out = Carbon::parse($a->time_out);
                if ($out->lt($in)) $out->addDay();
                $hrs = $in->diffInHours($out);
                return max(0, $hrs - 8) * 60;
            });
        $otHours = round($otMinutes / 60, 2);

        // 3) Night Differential (22:00–06:00, whole hours)
        $ndHours = Attendance::where('employee_id', $empId)
            ->whereBetween('time_in', [$periodStart, $periodEnd])
            ->whereNotNull('time_out')
            ->get()
            ->sum(function ($a) {
                $in  = Carbon::parse($a->time_in);
                $out = Carbon::parse($a->time_out);
                if ($out->lt($in)) $out->addDay();

                $ndStart = $in->copy()->setTime(22,0);
                $ndEnd   = $in->copy()->setTime(6,0)->addDay();

                $startND = $in->gt($ndStart) ? $in  : $ndStart;
                $endND   = $out->lt($ndEnd)  ? $out : $ndEnd;

                if ($endND->gt($startND)) {
                    return intdiv($startND->diffInMinutes($endND), 60);
                }
                return 0;
            });

        // 4) Base rate & pays
        $baseRate = (float) (optional($employee->designation)->rate_per_hour ?: 0);
        $basePay  = round($workedHours * $baseRate, 2);
        $otPay    = round($otHours * $baseRate * 1.25, 2);
        $ndPay    = round($ndHours * $baseRate * 0.10, 2);

        $gross = round($basePay + $otPay + $ndPay, 2);

        // 5) Deductions
        $loanDed = (float) Loan::where('employee_id', $empId)
            ->where('status', 'active')
            ->sum('monthly_amount');

        $applyLoan = $periodStart->copy()->day(15)->betweenIncluded($periodStart, $periodEnd);
        $loan = $applyLoan ? round($loanDed, 2) : 0.0;

        $sss = $phil = $pag = 0.0;
        $applyGovt = $periodEnd->isSameDay($periodEnd->copy()->endOfMonth());
        if ($applyGovt && $gross > 0) {
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

        Payslip::create([
            'user_id'      => Auth::id(),
            'period_start' => $periodStart->toDateString(),
            'period_end'   => $periodEnd->toDateString(),
            'worked_hours' => $workedHours,
            'ot_hours'     => $otHours,
            'ot_pay'       => $otPay,
            'deductions'   => $deductions,
            'gross_amount' => $gross,
            'net_amount'   => $net,
        ]);

        return back()->with('success', 'Payslip generated.');
    }

    public function download(Payslip $payslip)
    {
        abort_unless($payslip->user_id === Auth::id(), 403);

        $employee    = Auth::user()->employee;
        $empId       = $employee->id;
        $periodStart = $payslip->period_start->copy()->startOfDay();
        $periodEnd   = $payslip->period_end->copy()->endOfDay();

        // Recompute breakdown for the PDF
        $minutes = Attendance::where('employee_id', $empId)
            ->whereBetween('time_in', [$periodStart, $periodEnd])
            ->whereNotNull('time_out')
            ->get()
            ->sum(function ($a) {
                $in  = Carbon::parse($a->time_in);
                $out = Carbon::parse($a->time_out);
                if ($out->lt($in)) $out->addDay();
                return $in->diffInMinutes($out);
            });
        $workedHours = round($minutes / 60, 2);

        $otMinutes = Attendance::where('employee_id', $empId)
            ->whereBetween('time_in', [$periodStart, $periodEnd])
            ->whereNotNull('time_out')
            ->get()
            ->sum(function ($a) {
                $in  = Carbon::parse($a->time_in);
                $out = Carbon::parse($a->time_out);
                if ($out->lt($in)) $out->addDay();
                $hrs = $in->diffInHours($out);
                return max(0, $hrs - 8) * 60;
            });
        $otHours = round($otMinutes / 60, 2);

        $ndHours = Attendance::where('employee_id', $empId)
            ->whereBetween('time_in', [$periodStart, $periodEnd])
            ->whereNotNull('time_out')
            ->get()
            ->sum(function ($a) {
                $in  = Carbon::parse($a->time_in);
                $out = Carbon::parse($a->time_out);
                if ($out->lt($in)) $out->addDay();

                $ndStart = $in->copy()->setTime(22,0);
                $ndEnd   = $in->copy()->setTime(6,0)->addDay();

                $startND = $in->gt($ndStart) ? $in  : $ndStart;
                $endND   = $out->lt($ndEnd)  ? $out : $ndEnd;

                if ($endND->gt($startND)) {
                    return intdiv($startND->diffInMinutes($endND), 60);
                }
                return 0;
            });

        $baseRate = (float) (optional($employee->designation)->rate_per_hour ?: 0);
        $basePay  = round($workedHours * $baseRate, 2);
        $otPay    = round($otHours * $baseRate * 1.25, 2);
        $ndPay    = round($ndHours * $baseRate * 0.10, 2);
        $gross    = round($basePay + $otPay + $ndPay, 2);

        // Deductions breakdown again (for clarity in PDF)
        $loanMonthly = (float) Loan::where('employee_id', $empId)
            ->where('status', 'active')
            ->sum('monthly_amount');

        $loan = $periodStart->copy()->day(15)->betweenIncluded($periodStart, $periodEnd)
              ? round($loanMonthly, 2) : 0.0;

        $sss = $phil = $pag = 0.0;
        if ($periodEnd->isSameDay($periodEnd->copy()->endOfMonth()) && $gross > 0) {
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

        $pdf = Pdf::loadView('payslips.pdf', [
            'payslip'      => $payslip,
            'base_rate'    => $baseRate,
            'base_pay'     => $basePay,
            'worked_hours' => $workedHours,
            'ot_hours'     => $otHours,
            'ot_pay'       => $otPay,
            'nd_hours'     => $ndHours,
            'nd_pay'       => $ndPay,
            // deductions
            'loan'         => $loan,
            'sss'          => $sss,
            'phil'         => $phil,
            'pag'          => $pag,
            'deductions'   => $deductions,
            // totals
            'gross'        => $gross,
            'net'          => $net,
        ]);

        if (ob_get_length()) { @ob_end_clean(); }
        return $pdf->stream("payslip-{$payslip->id}.pdf");
    }
}
