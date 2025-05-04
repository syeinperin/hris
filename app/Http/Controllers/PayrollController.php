<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Carbon\Carbon;
use PDF; // Barryvdh\DomPDF\Facade\Pdf

class PayrollController extends Controller
{
    /**
     * Display the payroll report (summary table).
     */
    public function index(Request $request)
    {
        // 1) Parse date range & search
        $startDateInput = $request->input('start_date');
        $endDateInput   = $request->input('end_date');
        $search         = $request->input('search');

        $start_date = $startDateInput
            ? Carbon::parse($startDateInput)->startOfDay()
            : Carbon::now()->startOfMonth()->startOfDay();

        $end_date = $endDateInput
            ? Carbon::parse($endDateInput)->endOfDay()
            : Carbon::now()->endOfMonth()->endOfDay();

        // 2) Build query with eager-loads: designation, schedule, attendances, deductions
        $query = Employee::with([
            'designation',
            'schedule',
            'attendances' => fn($q) => $q->whereBetween('time_in', [$start_date, $end_date]),
            'deductions',
        ]);

        // 3) Apply search filter
        if (! empty($search)) {
            $term = "%{$search}%";
            $query->where(fn($q) =>
                $q->where('name',  'like', $term)
                  ->orWhere('email', 'like', $term)
                  ->orWhere('employee_code','like', $term)
            );
        }

        // 4) Paginate results
        $employees = $query->paginate(10)->withQueryString();

        // 5) Compute worked/overtime & pay
        foreach ($employees as $emp) {
            $rph         = $emp->designation->rate_per_hour ?? 0;
            $totalSec    = 0;
            $overtimeSec = 0;

            // Pull the employee's 8-hr shift times
            $shiftIn  = $emp->schedule?->time_in;
            $shiftOut = $emp->schedule?->time_out;

            foreach ($emp->attendances as $att) {
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

                // If the shift is defined, calculate any OT beyond it
                if ($shiftIn && $shiftOut) {
                    $schedIn  = Carbon::parse($shiftIn)
                                     ->setDate($in->year, $in->month, $in->day);
                    $schedOut = Carbon::parse($shiftOut)
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

            // Convert to hours & money
            $workedHours   = round($totalSec    / 3600, 2);
            $overtimeHours = round($overtimeSec / 3600, 2);
            $grossPay      = round($rph * $workedHours,   2);
            $overtimePay   = round($rph * $overtimeHours, 2);

            // Sum deductions active in this period
            $deductions = $emp->deductions()
                ->where('effective_from', '<=', $end_date->toDateString())
                ->where(fn($q) => $q
                    ->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $start_date->toDateString())
                )
                ->sum('amount');

            $netPay = round($grossPay + $overtimePay - $deductions, 2);

            // Attach computed values for the Blade
            $emp->rate_per_hour   = $rph;
            $emp->worked_hours    = $workedHours;
            $emp->overtime_hours  = $overtimeHours;
            $emp->gross_pay       = $grossPay;
            $emp->overtime_pay    = $overtimePay;
            $emp->total_deduction = $deductions;
            $emp->net_pay         = $netPay;
        }

        return view('payroll.index', compact('employees', 'start_date', 'end_date'));
    }

    /**
     * Show details for a single employee's payslip.
     */
    public function show($id, Request $request)
    {
        // Date range parsing (same as index)
        $start_date = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : Carbon::now()->startOfMonth()->startOfDay();

        $end_date = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : Carbon::now()->endOfMonth()->endOfDay();

        // Eager-load designation, schedule, filtered attendances, deductions
        $emp = Employee::with([
            'designation',
            'schedule',
            'attendances' => fn($q) => $q->whereBetween('time_in', [$start_date, $end_date]),
            'deductions',
        ])->findOrFail($id);

        $rph         = $emp->designation->rate_per_hour ?? 0;
        $totalSec    = 0;
        $overtimeSec = 0;
        $details     = [];

        $shiftIn  = $emp->schedule?->time_in;
        $shiftOut = $emp->schedule?->time_out;

        foreach ($emp->attendances as $att) {
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

            $otSec = 0;
            if ($shiftIn && $shiftOut) {
                $schedIn  = Carbon::parse($shiftIn)
                                 ->setDate($in->year, $in->month, $in->day);
                $schedOut = Carbon::parse($shiftOut)
                                 ->setDate($in->year, $in->month, $in->day);
                if ($schedOut->lt($schedIn)) {
                    $schedOut->addDay();
                }
                $scheduledSec = $schedIn->diffInSeconds($schedOut);
                if ($workedSec > $scheduledSec) {
                    $otSec       = $workedSec - $scheduledSec;
                    $overtimeSec += $otSec;
                }
            }

            $details[] = [
                'date'           => $in->format('Y-m-d'),
                'time_in'        => $in->format('H:i:s'),
                'time_out'       => $out->format('H:i:s'),
                'worked_hours'   => round($workedSec / 3600, 2),
                'overtime_hours' => round($otSec     / 3600, 2),
            ];
        }

        // Final totals
        $workedHours   = round($totalSec    / 3600, 2);
        $overtimeHours = round($overtimeSec / 3600, 2);
        $grossPay      = round($rph * $workedHours,   2);
        $overtimePay   = round($rph * $overtimeHours, 2);

        $deductions = $emp->deductions()
            ->where('effective_from', '<=', $end_date->toDateString())
            ->where(fn($q) => $q
                ->whereNull('effective_until')
                ->orWhere('effective_until', '>=', $start_date->toDateString())
            )
            ->sum('amount');

        $netPay = round($grossPay + $overtimePay - $deductions, 2);

        return view('payroll.show', compact(
            'emp','details','rph',
            'workedHours','overtimeHours',
            'grossPay','overtimePay',
            'deductions','netPay',
            'start_date','end_date'
        ));
    }

    /**
     * Export the payroll summary (or selected employees) as a PDF.
     */
    public function exportPdf(Request $request)
    {
        // Same date parsing
        $start_date = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : Carbon::now()->startOfMonth()->startOfDay();

        $end_date = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : Carbon::now()->endOfMonth()->endOfDay();

        // Build query
        $query = Employee::with([
            'designation',
            'schedule',
            'attendances' => fn($q) => $q->whereBetween('time_in', [$start_date, $end_date]),
            'deductions',
        ]);

        // Optional filters by IDs or search
        if ($ids = $request->input('employee_ids')) {
            $query->whereIn('id', $ids);
        } elseif ($search = $request->input('search')) {
            $term = "%{$search}%";
            $query->where(fn($q) =>
                $q->where('name', 'like', $term)
                  ->orWhere('email','like', $term)
                  ->orWhere('employee_code','like',$term)
            );
        }

        $employees = $query->get();

        // Compute details per employee (similar to index + show)
        foreach ($employees as $emp) {
            $rph         = $emp->designation->rate_per_hour ?? 0;
            $totalSec    = 0;
            $overtimeSec = 0;
            $details     = [];

            $shiftIn  = $emp->schedule?->time_in;
            $shiftOut = $emp->schedule?->time_out;

            foreach ($emp->attendances as $att) {
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

                $otSec = 0;
                if ($shiftIn && $shiftOut) {
                    $schedIn  = Carbon::parse($shiftIn)
                                     ->setDate($in->year, $in->month, $in->day);
                    $schedOut = Carbon::parse($shiftOut)
                                     ->setDate($in->year, $in->month, $in->day);
                    if ($schedOut->lt($schedIn)) {
                        $schedOut->addDay();
                    }
                    $scheduledSec = $schedIn->diffInSeconds($schedOut);
                    if ($workedSec > $scheduledSec) {
                        $otSec       = $workedSec - $scheduledSec;
                        $overtimeSec += $otSec;
                    }
                }

                $details[] = [
                    'date'           => $in->format('Y-m-d'),
                    'time_in'        => $in->format('H:i:s'),
                    'time_out'       => $out->format('H:i:s'),
                    'worked_hours'   => round($workedSec / 3600, 2),
                    'overtime_hours' => round($otSec     / 3600, 2),
                ];
            }

            $workedHours   = round($totalSec    / 3600, 2);
            $overtimeHours = round($overtimeSec / 3600, 2);
            $grossPay      = round($rph * $workedHours,   2);
            $overtimePay   = round($rph * $overtimeHours, 2);

            $deductions = $emp->deductions()
                ->where('effective_from','<=',$end_date->toDateString())
                ->where(fn($q)=> $q
                    ->whereNull('effective_until')
                    ->orWhere('effective_until','>=',$start_date->toDateString())
                )
                ->sum('amount');

            $netPay = round($grossPay + $overtimePay - $deductions, 2);

            // Attach for PDF view
            $emp->detailed_attendances = $details;
            $emp->rate_per_hour        = $rph;
            $emp->worked_hours         = $workedHours;
            $emp->overtime_hours       = $overtimeHours;
            $emp->gross_pay            = $grossPay;
            $emp->overtime_pay         = $overtimePay;
            $emp->total_deduction      = $deductions;
            $emp->net_pay              = $netPay;
        }

        $pdf = PDF::loadView('payroll.pdf', [
            'employees'  => $employees,
            'start_date' => $start_date->toDateString(),
            'end_date'   => $end_date->toDateString(),
        ]);

        return $pdf->download('PayrollSummary.pdf');
    }
}