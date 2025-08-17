<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\LeaveRequest; // ⬅️ added
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Pagination\LengthAwarePaginator;

class AttendanceController extends Controller
{
    /**
     * Display the attendance kiosk (time in/out).
     */
    public function log()
    {
        return view('attendance.log');
    }

    /**
     * Handle kiosk clock-in / clock-out.
     */
    public function logAttendance(Request $request)
    {
        $request->validate([
            'attendance_type' => 'required|in:time_in,time_out',
            'employee_code'   => 'nullable|string',
            'employee_name'   => 'nullable|string',
        ]);

        $code = trim($request->input('employee_code'));
        $name = trim($request->input('employee_name'));

        // lookup by name ↔ code
        if (! $code && $name) {
            $emp  = Employee::where('name', $name)->firstOrFail();
            $code = $emp->employee_code;
        }
        if (! $name && $code) {
            $emp  = Employee::where('employee_code', $code)->firstOrFail();
            $name = $emp->name;
        }
        if (! $code) {
            return back()->withInput()->with('error', 'Please provide either code or name.');
        }
        $emp = Employee::where('employee_code', $code)->firstOrFail();

        if ($request->attendance_type === 'time_in') {
            $today   = Carbon::today()->toDateString();
            $already = Attendance::where('employee_id', $emp->id)
                                 ->whereDate('time_in', $today)
                                 ->exists();
            if ($already) {
                return back()->withInput()->with('error', 'You have already clocked in today.');
            }
            Attendance::create([
                'employee_id' => $emp->id,
                'time_in'     => Carbon::now(),
                'time_out'    => null,
            ]);
            return back()->with('success', 'Time-in recorded.');
        }

        // time out
        $open = Attendance::where('employee_id', $emp->id)
                          ->whereNull('time_out')
                          ->latest('time_in')
                          ->first();
        if (! $open) {
            return back()->withInput()->with('error', 'No open clock-in to clock out.');
        }
        $open->update(['time_out' => Carbon::now()]);
        return back()->with('success', 'Time-out recorded.');
    }

    /**
     * Build index: [employee_id][Y-m-d] => LeaveRequest (approved).
     */
    private function buildLeaveIndex(string $startDate, string $endDate): array
    {
        $leaves = LeaveRequest::where('status', 'approved')
            ->whereDate('start_date', '<=', $endDate)
            ->whereDate('end_date',   '>=', $startDate)
            ->get();

        $idx = [];
        foreach ($leaves as $lv) {
            $from = Carbon::parse($lv->start_date)->max($startDate);
            $to   = Carbon::parse($lv->end_date)->min($endDate);
            foreach (CarbonPeriod::create($from, $to) as $d) {
                $idx[$lv->employee_id][$d->toDateString()] = $lv;
            }
        }
        return $idx;
    }

    /**
     * Convert minutes late to hours rounded up to the next 0.25h,
     * capped at 23h45m (23.75).
     */
    private function lateHoursFromMinutes(int $mins): float
    {
        if ($mins <= 0) return 0.0;
        $hours = ceil($mins / 15) * 0.25;   // 1–15m => 0.25, 16–30m => 0.50, etc.
        return min($hours, 23.75);          // cap; change to 23.0 if you prefer 23h max
    }

    /**
     * List attendance records (admin), with filters & pagination.
     */
    public function index(Request $request)
    {
        $search    = $request->input('search', '');
        $startDate = $request->input('start_date')
                   ? Carbon::parse($request->input('start_date'))->toDateString()
                   : Carbon::today()->toDateString();
        $endDate   = $request->input('end_date')
                   ? Carbon::parse($request->input('end_date'))->toDateString()
                   : Carbon::today()->toDateString();
        $statusF   = $request->input('status', '');

        // Approved leave index for the window
        $leaveIndex = $this->buildLeaveIndex($startDate, $endDate);

        // 1) Fetch all active employees
        $employees = Employee::where('status', 'active')
                             ->orderBy('name')
                             ->get();

        // 2) Build rows: one per employee per day
        $rows = [];
        foreach (CarbonPeriod::create($startDate, $endDate) as $day) {
            $date = $day->toDateString();

            foreach ($employees as $emp) {
                // If on approved leave that day, short-circuit with a leave row
                if (!empty($leaveIndex[$emp->id][$date])) {
                    $lv = $leaveIndex[$emp->id][$date];
                    $rows[] = [
                        'id'            => null,
                        'employee_id'   => $emp->id,
                        'employee_code' => $emp->employee_code,
                        'employee_name' => $emp->name,
                        'time_in'       => '—',
                        'time_out'      => '—',
                        'date'          => $date,
                        'ot_hours'      => '',
                        'status'        => 'On Leave ('.ucwords(str_replace('_',' ',$lv->leave_type)).')',
                        'late_hours'    => '',
                    ];
                    continue;
                }

                $att = Attendance::where('employee_id', $emp->id)
                                 ->whereDate('time_in', $date)
                                 ->first();

                // static schedule relation
                $sched = $emp->schedule;

                if ($att) {
                    $in  = Carbon::parse($att->time_in);
                    $out = $att->time_out
                         ? Carbon::parse($att->time_out)
                         : null;

                    // compute work seconds (handles overnight)
                    $workSec = 0;
                    if ($out) {
                        if ($out->lt($in)) {
                            $out->addDay();
                        }
                        $workSec = $in->diffInSeconds($out);
                    }

                    // scheduled seconds
                    $schedSec = 0;
                    if ($sched && $sched->time_in) {
                        $sIn  = Carbon::parse($sched->time_in)
                                     ->setDate($day->year, $day->month, $day->day);
                        $sOut = Carbon::parse($sched->time_out)
                                     ->setDate($day->year, $day->month, $day->day);
                        if ($sOut->lt($sIn)) {
                            $sOut->addDay();
                        }
                        $schedSec = $sIn->diffInSeconds($sOut);
                    }

                    // overtime hours
                    $otHours = ($schedSec > 0 && $workSec > $schedSec)
                             ? round(($workSec - $schedSec) / 3600, 2)
                             : 0;

                    // status & late hours
                    $status    = 'On Time';
                    $lateHours = '';
                    if ($sched && $sched->time_in) {
                        $sIn      = Carbon::parse($sched->time_in)
                                          ->setDate($day->year, $day->month, $day->day);
                        if ($in->gt($sIn)) {
                            $status    = 'Late';
                            $minsLate  = $sIn->diffInMinutes($in);
                            $lateHours = $this->lateHoursFromMinutes($minsLate);
                        }
                    }

                    $rows[] = [
                        'id'            => $att->id,
                        'employee_id'   => $emp->id,
                        'employee_code' => $emp->employee_code,
                        'employee_name' => $emp->name,
                        'time_in'       => $in->format('h:i:s A'),
                        'time_out'      => $out?->format('h:i:s A') ?? 'Still in',
                        'date'          => $date,
                        'ot_hours'      => $otHours,
                        'status'        => $status,
                        'late_hours'    => $lateHours,
                    ];
                } else {
                    // absent
                    $rows[] = [
                        'id'            => null,
                        'employee_id'   => $emp->id,
                        'employee_code' => $emp->employee_code,
                        'employee_name' => $emp->name,
                        'time_in'       => 'N/A',
                        'time_out'      => 'N/A',
                        'date'          => $date,
                        'ot_hours'      => '',
                        'status'        => 'Absent',
                        'late_hours'    => '',
                    ];
                }
            }
        }

        // 3) Apply filters
        if ($search !== '') {
            $rows = array_filter($rows, fn($r) =>
                str_contains(strtolower($r['employee_code']), strtolower($search)) ||
                str_contains(strtolower($r['employee_name']), strtolower($search))
            );
        }
        if ($statusF !== '') {
            $rows = array_filter($rows, fn($r) => $r['status'] === $statusF);
        }

        // 4) Sort & paginate
        usort($rows, fn($a, $b) =>
            [$a['date'], $a['employee_code']] <=> [$b['date'], $b['employee_code']]
        );
        $page    = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $slice   = array_slice($rows, ($page - 1) * $perPage, $perPage, true);
        $attendances = new LengthAwarePaginator(
            $slice,
            count($rows),
            $perPage,
            $page,
            ['path' => route('attendance.index'), 'query' => $request->query()]
        );

        return view('attendance.index', compact('attendances', 'search', 'startDate', 'endDate'));
    }

    /**
     * Show one employee’s full-month attendance breakdown.
     */
    public function show(Request $request, $id)
    {
        $employee     = Employee::with('schedule')->findOrFail($id);
        $month        = $request->input('month', Carbon::now()->format('Y-m'));
        $startOfMonth = Carbon::parse("$month-01")->startOfMonth();
        $endOfMonth   = (clone $startOfMonth)->endOfMonth();

        // Leave index for this employee/month
        $leaveIndex = $this->buildLeaveIndex($startOfMonth->toDateString(), $endOfMonth->toDateString());

        $rows = [];
        foreach (CarbonPeriod::create($startOfMonth, $endOfMonth) as $day) {
            $dateStr = $day->toDateString();

            // Leave row?
            if (!empty($leaveIndex[$employee->id][$dateStr])) {
                $lv = $leaveIndex[$employee->id][$dateStr];
                $rows[] = [
                    'date'       => $dateStr,
                    'time_in'    => '—',
                    'time_out'   => '—',
                    'ot_hours'   => '',
                    'status'     => 'On Leave ('.ucwords(str_replace('_',' ',$lv->leave_type)).')',
                    'late_hours' => '',
                ];
                continue;
            }

            $att = Attendance::where('employee_id', $id)
                             ->whereDate('time_in', $dateStr)
                             ->first();

            // static schedule relation
            $sched = $employee->schedule;

            if ($att) {
                $in  = Carbon::parse($att->time_in);
                $out = $att->time_out ? Carbon::parse($att->time_out) : null;

                // work seconds (overnight-aware)
                $workSec = 0;
                if ($out) {
                    if ($out->lt($in)) {
                        $out->addDay();
                    }
                    $workSec = $in->diffInSeconds($out);
                }

                // scheduled seconds
                $schedSec = 0;
                if ($sched && $sched->time_in) {
                    $sIn  = Carbon::parse($sched->time_in)
                                 ->setDate($day->year, $day->month, $day->day);
                    $sOut = Carbon::parse($sched->time_out)
                                 ->setDate($day->year, $day->month, $day->day);
                    if ($sOut->lt($sIn)) {
                        $sOut->addDay();
                    }
                    $schedSec = $sIn->diffInSeconds($sOut);
                }

                // overtime hours
                $otHours = ($schedSec > 0 && $workSec > $schedSec)
                         ? round(($workSec - $schedSec) / 3600, 2)
                         : 0;

                // status & late hours
                $status    = 'On Time';
                $lateHours = '';
                if ($sched && $sched->time_in) {
                    $sIn      = Carbon::parse($sched->time_in)
                                      ->setDate($day->year, $day->month, $day->day);
                    if ($in->gt($sIn)) {
                        $status    = 'Late';
                        $minsLate  = $sIn->diffInMinutes($in);
                        $lateHours = $this->lateHoursFromMinutes($minsLate);
                    }
                }

                $rows[] = [
                    'date'        => $dateStr,
                    'time_in'     => $in->format('h:i:s A'),
                    'time_out'    => $out?->format('h:i:s A') ?? '—',
                    'ot_hours'    => $otHours,
                    'status'      => $status,
                    'late_hours'  => $lateHours,
                ];
            } else {
                // absent
                $rows[] = [
                    'date'       => $dateStr,
                    'time_in'    => '—',
                    'time_out'   => '—',
                    'ot_hours'   => '',
                    'status'     => 'Absent',
                    'late_hours' => '',
                ];
            }
        }

        return view('attendance.show', compact('employee', 'month', 'startOfMonth', 'endOfMonth', 'rows'));
    }

    /**
     * Delete an attendance record.
     */
    public function destroy($id)
    {
        Attendance::findOrFail($id)->delete();
        return back()->with('success', 'Attendance record deleted.');
    }
}
