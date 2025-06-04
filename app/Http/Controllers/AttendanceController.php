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
     * Accepts either employee_code or employee_name (one is required).
     */
    public function logAttendance(Request $request)
    {
        // 1) Validate presence of attendance_type; code/name both nullable strings
        $request->validate([
            'attendance_type' => 'required|in:time_in,time_out',
            'employee_code'   => 'nullable|string',
            'employee_name'   => 'nullable|string',
        ]);

        $code = trim($request->input('employee_code'));
        $name = trim($request->input('employee_name'));

        // 2) If code is empty but name provided ⇒ look up code by name
        if (empty($code) && ! empty($name)) {
            $empByName = Employee::where('name', $name)->first();
            if (! $empByName) {
                return back()->withInput()->with('error', 'Employee name not found.');
            }
            $code = $empByName->employee_code;
        }

        // 3) If name is empty but code provided ⇒ look up name by code
        if (empty($name) && ! empty($code)) {
            $empByCode = Employee::where('employee_code', $code)->first();
            if (! $empByCode) {
                return back()->withInput()->with('error', 'Employee code not found.');
            }
            $name = $empByCode->name;
        }

        // 4) Now code must be non-empty (one of code/name was required)
        if (empty($code)) {
            return back()->withInput()->with('error', 'Please provide either Employee Code or Employee Name.');
        }

        // 5) Fetch the Employee by code
        $emp = Employee::where('employee_code', $code)->first();
        if (! $emp) {
            return back()->withInput()->with('error', 'Employee not found for code: ' . $code);
        }

        // 6) TIME IN logic
        if ($request->attendance_type === 'time_in') {
            $today     = Carbon::today()->toDateString();
            $alreadyIn = Attendance::where('employee_id', $emp->id)
                                   ->whereDate('time_in', $today)
                                   ->exists();

            if ($alreadyIn) {
                return back()->withInput()->with('error', 'You have already clocked in today.');
            }

            $att = Attendance::create([
                'employee_id' => $emp->id,
                'time_in'     => Carbon::now(),
                'time_out'    => null,
            ]);

            return back()->with('success', 'Time-in recorded at ' . $att->time_in->format('h:i:s A'));
        }

        // 7) TIME OUT logic
        $att = Attendance::where('employee_id', $emp->id)
                         ->whereNull('time_out')
                         ->orderBy('time_in', 'desc')
                         ->first();

        if (! $att) {
            return back()->withInput()->with('error', 'No open clock-in found to clock out.');
        }

        $att->time_out = Carbon::now();
        $att->save();

        return back()->with('success', 'Time-out recorded at ' . $att->time_out->format('h:i:s A'));
    }

    /**
     * AJAX: Given a code, return { name } JSON.
     * GET /attendance/employee/{code}
     */
    public function employeeInfo($code)
    {
        $emp = Employee::where('employee_code', $code)->first();
        return response()->json(['name' => $emp?->name]);
    }

    /**
     * AJAX: Given a name, return { code } JSON.
     * GET /attendance/code/{name}
     */
    public function employeeCodeFromName($name)
    {
        $emp = Employee::where('name', $name)->first();
        return response()->json(['code' => $emp?->employee_code]);
    }

    /**
     * Admin: Display the attendance list with date-range, filters & pagination.
     */
    public function index(Request $request)
    {
        // … your existing logic for listing/filtering/paginating attendance records …
        $search       = $request->input('search');
        $empFilter    = $request->input('employee_name');
        $statusFilter = $request->input('status');

        $startDate = $request->filled('start_date')
                   ? Carbon::parse($request->input('start_date'))->toDateString()
                   : Carbon::today()->toDateString();
        $endDate   = $request->filled('end_date')
                   ? Carbon::parse($request->input('end_date'))->toDateString()
                   : Carbon::today()->toDateString();

        $employees = Employee::when($search, fn($q) =>
                                $q->where('employee_code','like',"%{$search}%")
                                  ->orWhere('name','like',"%{$search}%")
                            )
                            ->when($empFilter, fn($q) =>
                                $q->where('name', $empFilter)
                            )
                            ->orderBy('name')
                            ->get();

        $period = CarbonPeriod::create($startDate, $endDate);

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

        if (in_array($statusFilter, ['On Time','Late','Absent'])) {
            $rows = array_filter($rows, fn($r) => $r['status'] === $statusFilter);
        }

        usort($rows, fn($a, $b) => [$a['date'], $a['employee_code']] <=> [$b['date'], $b['employee_code']]);

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
     * Admin: Delete an attendance record.
     */
    public function destroy($id)
    {
        Attendance::findOrFail($id)->delete();
        return back()->with('success','Attendance record deleted.');
    }
}
