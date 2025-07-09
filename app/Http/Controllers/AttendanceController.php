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
        if (!$code && $name) {
            $emp = Employee::where('name',$name)->firstOrFail();
            $code = $emp->employee_code;
        }
        if (!$name && $code) {
            $emp = Employee::where('employee_code',$code)->firstOrFail();
            $name = $emp->name;
        }
        if (!$code) {
            return back()->withInput()->with('error','Please provide either code or name.');
        }
        $emp = Employee::where('employee_code',$code)->firstOrFail();

        if ($request->attendance_type === 'time_in') {
            $today   = Carbon::today()->toDateString();
            $already = Attendance::where('employee_id',$emp->id)
                                 ->whereDate('time_in',$today)
                                 ->exists();
            if ($already) {
                return back()->withInput()->with('error','You have already clocked in today.');
            }
            Attendance::create([
                'employee_id' => $emp->id,
                'time_in'     => Carbon::now(),
                'time_out'    => null,
            ]);
            return back()->with('success','Time-in recorded.');
        }

        // time out
        $open = Attendance::where('employee_id',$emp->id)
                          ->whereNull('time_out')
                          ->latest('time_in')
                          ->first();
        if (! $open) {
            return back()->withInput()->with('error','No open clock-in to clock out.');
        }
        $open->update(['time_out' => Carbon::now()]);
        return back()->with('success','Time-out recorded.');
    }

    /**
     * List attendance records (admin), with filters & pagination.
     */
    public function index(Request $request)
    {
        $search    = $request->input('search');
        $startDate = $request->input('start_date')
                   ? Carbon::parse($request->input('start_date'))->toDateString()
                   : Carbon::today()->toDateString();
        $endDate   = $request->input('end_date')
                   ? Carbon::parse($request->input('end_date'))->toDateString()
                   : Carbon::today()->toDateString();

        // only active employees, search code or name
        $employees = Employee::where('status','active')
            ->when($search, function($q,$s){
                $q->where('employee_code','like',"%{$s}%")
                  ->orWhere('name','like',        "%{$s}%");
            })
            ->orderBy('name')
            ->get();

        $rows = [];
        foreach (CarbonPeriod::create($startDate, $endDate) as $day) {
            $date = $day->toDateString();

            foreach ($employees as $emp) {
                $att = Attendance::where('employee_id',$emp->id)
                                 ->whereDate('time_in',$date)
                                 ->first();

                if ($att) {
                    $in     = Carbon::parse($att->time_in);
                    $out    = $att->time_out 
                              ? Carbon::parse($att->time_out) 
                              : null;

                    // determine status
                    if ($emp->schedule && $emp->schedule->time_in) {
                        $sched = Carbon::parse($emp->schedule->time_in)
                            ->setDate($in->year,$in->month,$in->day);
                        $status = $in->gt($sched) ? 'Late' : 'On Time';
                    } else {
                        $status = 'On Time';
                    }

                    $rows[] = [
                        'id'            => $att->id,
                        'employee_id'   => $emp->id,
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
                        'employee_id'   => $emp->id,
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

        // filter by status if provided
        if ($sf = $request->input('status')) {
            $rows = array_filter($rows, fn($r)=> $r['status']===$sf);
        }

        // sort by date, then code
        usort($rows, fn($a,$b)=>
            [$a['date'],$a['employee_code']]
            <=>
            [$b['date'],$b['employee_code']]
        );

        $page    = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $slice   = array_slice($rows, ($page-1)*$perPage, $perPage, true);

        $attendances = new LengthAwarePaginator(
            $slice,
            count($rows),
            $perPage,
            $page,
            ['path'=>route('attendance.index'),'query'=>$request->query()]
        );

        return view('attendance.index', compact(
            'attendances','search','startDate','endDate'
        ));
    }

    /**
     * Show one employee’s full-month attendance breakdown.
     */
    public function show(Request $request, $attendance)
    {
        // here $attendance is actually the employee id
        $employeeId  = $attendance;
        $month       = $request->input('month', Carbon::today()->format('Y-m'));
        $startOfMonth= Carbon::parse("$month-01")->startOfMonth();
        $endOfMonth  = (clone $startOfMonth)->endOfMonth();

        $employee = Employee::with('schedule')->findOrFail($employeeId);

        $rows = [];
        foreach (CarbonPeriod::create($startOfMonth, $endOfMonth) as $day) {
            $dateStr = $day->toDateString();
            $att = Attendance::where('employee_id',$employeeId)
                             ->whereDate('time_in',$dateStr)
                             ->first();

            if ($att) {
                $in     = Carbon::parse($att->time_in);
                $out    = $att->time_out ? Carbon::parse($att->time_out) : null;
                $status = 'On Time';
                if ($employee->schedule && $employee->schedule->time_in) {
                    $sched = Carbon::parse($employee->schedule->time_in)
                              ->setDate($in->year,$in->month,$in->day);
                    $status = $in->gt($sched) ? 'Late' : 'On Time';
                }
                $rows[] = [
                    'date'     => $dateStr,
                    'time_in'  => $in->format('h:i:s A'),
                    'time_out' => $out?->format('h:i:s A') ?? '—',
                    'status'   => $status,
                ];
            } else {
                $rows[] = [
                    'date'     => $dateStr,
                    'time_in'  => '—',
                    'time_out' => '—',
                    'status'   => 'Absent',
                ];
            }
        }

        return view('attendance.show', compact(
            'employee','rows','startOfMonth','endOfMonth','month'
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