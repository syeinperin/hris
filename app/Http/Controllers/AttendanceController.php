<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;
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

        $emp = Employee::where('employee_code', $request->employee_code)
                       ->firstOrFail();

        if ($request->attendance_type === 'time_in') {
            // CLOCK IN
            $today = Carbon::today()->toDateString();

            // reuse existing today record or new
            $att = Attendance::firstOrNew([
                'employee_id' => $emp->id,
                'time_in'     => Attendance::where('employee_id', $emp->id)
                                           ->whereDate('time_in', $today)
                                           ->value('time_in'),
            ]);

            $att->time_in  = Carbon::now();
            $att->time_out = null;
            $att->save();

            return back()
                ->with('success', 'Clock-in recorded at ' . $att->time_in->format('h:i:s A'));
        }

        // CLOCK OUT
        $att = Attendance::where('employee_id', $emp->id)
                         ->whereNull('time_out')
                         ->orderBy('time_in', 'desc')
                         ->first();

        if (! $att) {
            return back()->with('error', 'No open clock-in found to punch out.');
        }

        $att->time_out = Carbon::now();
        $att->save();

        return back()
            ->with('success', 'Clock-out recorded at ' . $att->time_out->format('h:i:s A'));
    }

    /**
     * AJAX lookup: return employee name for given code.
     * GET /attendance/employee/{code}
     */
    public function employeeInfo($code)
    {
        $emp = Employee::where('employee_code', $code)->first();
        return response()->json(['name' => $emp?->name]);
    }

    /**
     * Display the attendance list with filters & pagination.
     */
    public function index(Request $request)
    {
        $search       = $request->input('search');
        $empFilter    = $request->input('employee_name');
        $statusFilter = $request->input('status');
        $dateFilter   = $request->filled('date')
                        ? $request->input('date')
                        : Carbon::today()->toDateString();

        // 1) All employees for dropdown + table
        $employees = Employee::when($search, fn($q) =>
                            $q->where('employee_code','like',"%{$search}%")
                              ->orWhere('name','like',"%{$search}%")
                        )
                        ->when($empFilter, fn($q) =>
                            $q->where('name', $empFilter)
                        )
                        ->orderBy('name')
                        ->get();

        // 2) Last 30 days of attendance dates
        $dates = Attendance::selectRaw('DATE(time_in) as dt')
                   ->where('time_in','>=', Carbon::today()->subDays(30))
                   ->groupBy('dt')
                   ->orderBy('dt','desc')
                   ->pluck('dt','dt')
                   ->toArray();

        // 3) Build each row
        $rows = [];
        foreach ($employees as $emp) {
            $att = Attendance::where('employee_id', $emp->id)
                             ->whereDate('time_in', $dateFilter)
                             ->first();

            if ($att) {
                $in  = Carbon::parse($att->time_in);
                $out = $att->time_out
                    ? Carbon::parse($att->time_out)
                    : null;

                // Determine Late vs On Time
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
                    'date'          => $dateFilter,
                    'status'        => $status,
                ];
            } else {
                $rows[] = [
                    'id'            => null,
                    'employee_code' => $emp->employee_code,
                    'employee_name' => $emp->name,
                    'time_in'       => 'N/A',
                    'time_out'      => 'N/A',
                    'date'          => $dateFilter,
                    'status'        => 'Absent',
                ];
            }
        }

        // 4) Filter by status if selected
        if (in_array($statusFilter, ['On Time','Late','Absent'])) {
            $rows = array_filter($rows, fn($r) => $r['status'] === $statusFilter);
        }

        // 5) Manual pagination
        $page    = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $slice   = array_slice($rows, ($page - 1) * $perPage, $perPage, true);

        $attendances = new LengthAwarePaginator(
            $slice,
            count($rows),
            $perPage,
            $page,
            [
              'path'  => route('attendance.index'),
              'query' => $request->query(),
            ]
        );

        return view('attendance.index', compact(
            'attendances','employees','dates','dateFilter','statusFilter','search','empFilter'
        ));
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
