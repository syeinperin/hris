<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Pagination\LengthAwarePaginator;

class AttendanceController extends Controller
{
    /**
     * Show the public kiosk form (GET /kiosk).
     */
    public function log()
    {
        return view('attendance.log');
    }

    /**
     * Handle kiosk punch (POST /kiosk): clock-in or clock-out.
     */
    public function logAttendance(Request $request)
    {
        $request->validate([
            'attendance_type' => 'required|in:time_in,time_out',
            'employee_code'   => 'required|string|exists:employees,employee_code',
        ]);

        $emp   = Employee::where('employee_code', $request->employee_code)->firstOrFail();

        if ($request->attendance_type === 'time_in') {
            $today     = Carbon::today()->toDateString();
            $alreadyIn = Attendance::where('employee_id', $emp->id)
                                   ->whereDate('time_in', $today)
                                   ->exists();

            if ($alreadyIn) {
                return back()->with('error', 'You have already clocked in today.');
            }

            $att = Attendance::create([
                'employee_id' => $emp->id,
                'time_in'     => Carbon::now(),
                'time_out'    => null,
            ]);

            return back()->with('success', 'Time-in recorded at ' . $att->time_in->format('h:i:s A'));
        }

        // time_out branch: find most recent open clock-in
        $att = Attendance::where('employee_id', $emp->id)
                         ->whereNull('time_out')
                         ->orderBy('time_in', 'desc')
                         ->first();

        if (! $att) {
            return back()->with('error', 'No open clock-in found to  out.');
        }

        $att->time_out = Carbon::now();
        $att->save();

        return back()->with('success', 'Time-out recorded at ' . $att->time_out->format('h:i:s A'));
    }

    /**
     * AJAX lookup: return employee name for given code.
     */
    public function employeeInfo($code)
    {
        $emp = Employee::where('employee_code', $code)->first();
        return response()->json(['name' => $emp?->name]);
    }

    /**
     * Display the attendance list with date-range, filters & pagination.
     */
    public function index(Request $request)
    {
        // Filters
        $search       = $request->input('search');
        $empFilter    = $request->input('employee_name');
        $statusFilter = $request->input('status');

        // Date range defaults to today if not set
        $startDate = $request->filled('start_date')
                   ? Carbon::parse($request->input('start_date'))->toDateString()
                   : Carbon::today()->toDateString();
        $endDate   = $request->filled('end_date')
                   ? Carbon::parse($request->input('end_date'))->toDateString()
                   : Carbon::today()->toDateString();

        // Fetch matching employees
        $employees = Employee::when($search, fn($q) =>
                                $q->where('employee_code','like',"%{$search}%")
                                  ->orWhere('name','like',"%{$search}%")
                            )
                            ->when($empFilter, fn($q) =>
                                $q->where('name', $empFilter)
                            )
                            ->orderBy('name')
                            ->get();

        // Build period of days
        $period = CarbonPeriod::create($startDate, $endDate);

        // Assemble rows
        $rows = [];
        foreach ($period as $day) {
            $date = $day->toDateString();
            foreach ($employees as $emp) {
                $att = Attendance::where('employee_id', $emp->id)
                                 ->whereDate('time_in', $date)
                                 ->first();

                if ($att) {
                    $in  = Carbon::parse($att->time_in);
                    $out = $att->time_out ? Carbon::parse($att->time_out) : null;

                    // Late vs On Time
                    if ($emp->schedule && $emp->schedule->time_in) {
                        $sched = Carbon::parse($emp->schedule->time_in)
                                      ->setDate($in->year, $in->month, $in->day);
                        $status = $in->greaterThan($sched) ? 'Late' : 'On Time';
                    } else {
                        $status = 'On Time';
                    }

                    $rows[] = [
                        'id'            => $att->id,
                        'employee_code' => $emp->employee_code,
                        'employee_name' => $emp->name,
                        'time_in'       => $in->format('h:i:s A'),
                        'time_out'      => $out?->format('h:i:s A') ?? 'Still in',
                        'date'          => $date,
                        'status'        => $status,
                    ];
                } else {
                    $rows[] = [
                        'id'            => null,
                        'employee_code' => $emp->employee_code,
                        'employee_name' => $emp->name,
                        'time_in'       => 'N/A',
                        'time_out'      => 'N/A',
                        'date'          => $date,
                        'status'        => 'Absent',
                    ];
                }
            }
        }

        // Filter by status
        if (in_array($statusFilter, ['On Time','Late','Absent'])) {
            $rows = array_filter($rows, fn($r) => $r['status'] === $statusFilter);
        }

        // Sort by date asc, then code asc
        usort($rows, fn($a, $b) => [$a['date'], $a['employee_code']] <=> [$b['date'], $b['employee_code']]);

        // Manual pagination
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

        return view('attendance.index', [
            'attendances'  => $attendances,
            'employees'    => $employees,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'search'       => $search,
            'empFilter'    => $empFilter,
            'statusFilter' => $statusFilter,
        ]);
    }

    /**
     * Delete an attendance record.
     */
    public function destroy($id)
    {
        Attendance::findOrFail($id)->delete();
        return back()->with('success','Attendance record deleted.');
    }
}
