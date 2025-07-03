<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $month  = $request->get('month', Carbon::now()->format('Y-m'));
        $search = $request->input('search', '');
        $start  = Carbon::parse("{$month}-01");
        $end    = (clone $start)->endOfMonth();

        $employees = Employee::active()
            ->when($search, fn($q,$s) =>
                $q->where('employee_code','like',"%{$s}%")
                  ->orWhere('name','like',"%{$s}%")
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
            ->map(fn($g) => $g->mapWithKeys(fn($r) => [
                $r->time_in->toDateString() => $r
            ]));

        $holidays = Holiday::forYear($start->year)
            ->pluck('name','date');

        return view('payroll.calendar', compact(
            'employees','attendance','holidays',
            'start','end','month','search'
        ));
    }

    /**
     * Toggle manual override attendance.
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

        if ($existing) {
            $existing->delete();
            $state = false;
        } else {
            Attendance::create([
                'employee_id'=>$data['employee_id'],
                'schedule_id'=>null,
                'time_in'    => "{$data['date']} 00:00:00",
                'time_out'   => null,
                'is_manual'  => true,
            ]);
            $state = true;
        }

        return response()->json(['manual'=>$state]);
    }
}
