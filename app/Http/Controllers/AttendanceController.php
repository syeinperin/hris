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

        // lookup by name → code
        if (empty($code) && ! empty($name)) {
            $emp = Employee::where('name',$name)->first();
            if (! $emp) {
                return back()->withInput()->with('error','Employee name not found.');
            }
            $code = $emp->employee_code;
        }

        // lookup by code → name
        if (empty($name) && ! empty($code)) {
            $emp = Employee::where('employee_code',$code)->first();
            if (! $emp) {
                return back()->withInput()->with('error','Employee code not found.');
            }
            $name = $emp->name;
        }

        if (empty($code)) {
            return back()->withInput()->with('error','Please provide either code or name.');
        }

        $emp = Employee::where('employee_code',$code)->first();
        if (! $emp) {
            return back()->withInput()->with('error',"Employee not found for code: {$code}");
        }

        if ($request->attendance_type==='time_in') {
            $today = Carbon::today()->toDateString();
            $already = Attendance::where('employee_id',$emp->id)
                ->whereDate('time_in',$today)
                ->exists();
            if ($already) {
                return back()->withInput()->with('error','You have already clocked in today.');
            }
            $att = Attendance::create([
                'employee_id' => $emp->id,
                'time_in'     => Carbon::now(),
                'time_out'    => null,
            ]);
            return back()->with('success','Time-in recorded at '.$att->time_in->format('h:i:s A'));
        }

        // time out
        $open = Attendance::where('employee_id',$emp->id)
            ->whereNull('time_out')
            ->orderBy('time_in','desc')
            ->first();
        if (! $open) {
            return back()->withInput()->with('error','No open clock-in to clock out.');
        }
        $open->update(['time_out'=>Carbon::now()]);
        return back()->with('success','Time-out recorded at '.$open->time_out->format('h:i:s A'));
    }

    /**
     * AJAX: return { name } for a given code.
     */
    public function employeeInfo($code)
    {
        $emp = Employee::where('employee_code',$code)->first();
        return response()->json(['name'=>$emp?->name]);
    }

    /**
     * AJAX: return { code } for a given name.
     */
    public function employeeCodeFromName($name)
    {
        $emp = Employee::where('name',$name)->first();
        return response()->json(['code'=>$emp?->employee_code]);
    }

    /**
     * List attendance records (admin), with filters & pagination.
     */
    public function index(Request $request)
    {
        $search    = $request->input('search');
        $startDate = $request->filled('start_date')
                   ? Carbon::parse($request->input('start_date'))->toDateString()
                   : Carbon::today()->toDateString();
        $endDate   = $request->filled('end_date')
                   ? Carbon::parse($request->input('end_date'))->toDateString()
                   : Carbon::today()->toDateString();

        // only active employees, search code or name
        $employees = Employee::where('status','active')
            ->when($search, fn($q,$s) =>
                $q->where('employee_code','like',"%{$s}%")
                  ->orWhere('name','like',"%{$s}%")
            )
            ->orderBy('name')
            ->get();

        $period = CarbonPeriod::create($startDate,$endDate);
        $rows = [];

        foreach ($period as $day) {
            $date = $day->toDateString();
            foreach ($employees as $emp) {
                $att = Attendance::where('employee_id',$emp->id)
                    ->whereDate('time_in',$date)
                    ->first();

                if ($att) {
                    $in  = Carbon::parse($att->time_in);
                    $out = $att->time_out ? Carbon::parse($att->time_out) : null;

                    // determine status
                    if ($emp->schedule && $emp->schedule->time_in) {
                        $sched = Carbon::parse($emp->schedule->time_in)
                            ->setDate($in->year,$in->month,$in->day);
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

        if ($sf = $request->input('status')) {
            $rows = array_filter($rows, fn($r)=> $r['status']===$sf);
        }

        usort($rows, fn($a,$b)=>
            [$a['date'],$a['employee_code']] <=> [$b['date'],$b['employee_code']]
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
            'attendances','employees','search','startDate','endDate'
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
