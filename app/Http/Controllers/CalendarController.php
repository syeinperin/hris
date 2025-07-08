<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CalendarController extends Controller
{
    /**
     * Render the interactive calendar.
     */
    public function index(Request $request)
    {
        $month      = $request->get('month', Carbon::now()->format('Y-m'));
        $search     = $request->input('search', '');
        $start      = Carbon::parse("{$month}-01");
        $end        = (clone $start)->endOfMonth();

        $employees  = Employee::where('status','active')
            ->when($search, fn($q,$s)=>
                $q->where('employee_code','like',"%{$s}%")
                  ->orWhere('name','like',         "%{$s}%")
            )
            ->with(['department','designation','schedule'])
            ->orderBy('name')
            ->paginate(25)
            ->appends(compact('month','search'));

        $attendance = Attendance::whereBetween('time_in', [
                $start->toDateString().' 00:00:00',
                $end->toDateString()  .' 23:59:59',
            ])
            ->whereIn('employee_id', $employees->pluck('id'))
            ->get()
            ->groupBy('employee_id')
            ->map(fn($grp)=>
                $grp->mapWithKeys(fn($r)=>[
                    $r->time_in->toDateString() => $r
                ])
            );

        $leaveIndex = LeaveRequest::whereIn('employee_id',$employees->pluck('id'))
            ->where(function($q) use($start,$end){
                $q->whereBetween('start_date',[$start->toDateString(),$end->toDateString()])
                  ->orWhereBetween('end_date',  [$start->toDateString(),$end->toDateString()]);
            })
            ->get()
            ->groupBy('employee_id')
            ->map(fn($grp)=>
                $grp->groupBy(fn($r)=>$r->start_date->toDateString())
            );

        $holidays = Holiday::whereYear('date',$start->year)
            ->pluck('name','date')
            ->all();

        return view('payroll.calendar', compact(
            'employees','attendance','leaveIndex',
            'holidays','start','end','month','search'
        ));
    }

    /**
     * Toggle manual override (POST).
     */
    public function toggleManual(Request $request)
    {
        $data = $request->validate([
            'employee_id'=>'required|exists:employees,id',
            'date'       =>'required|date',
        ]);

        $existing = Attendance::where('employee_id',$data['employee_id'])
            ->whereDate('time_in',$data['date'])
            ->where('is_manual',true)
            ->first();

        if($existing){
            $existing->delete();
            $state = false;
        } else {
            Attendance::create([
                'employee_id'=>$data['employee_id'],
                'schedule_id'=>null,
                'time_in'    =>"{$data['date']} 00:00:00",
                'time_out'   =>null,
                'is_manual'  =>true,
            ]);
            $state = true;
        }

        return response()->json(['manual' => $state]);
    }

    /**
     * Set biometric (auto) attendance (POST).
     */
    public function setBiometric(Request $request)
    {
        $data = $request->validate([
            'employee_id'=>'required|exists:employees,id',
            'date'       =>'required|date',
        ]);

        // either flip an existing manual to auto...
        $att = Attendance::firstOrNew([
            'employee_id'=> $data['employee_id'],
            'time_in'    => "{$data['date']} 00:00:00",
        ]);
        $att->is_manual = false;
        $att->save();

        return response()->json(['biometric' => true]);
    }

    /**
     * Delete any record for that cell (DELETE).
     */
    public function removeManual(Request $request)
    {
        $data = $request->validate([
            'employee_id'=>'required|exists:employees,id',
            'date'       =>'required|date',
        ]);

        Attendance::where('employee_id',$data['employee_id'])
            ->whereDate('time_in',$data['date'])
            ->delete();

        return response()->json(['removed' => true]);
    }
}
