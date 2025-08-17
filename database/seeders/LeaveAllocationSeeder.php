<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\LeaveAllocation;

class LeaveAllocationSeeder extends Seeder
{
    public function run(): void
    {
        $year       = Carbon::now()->year;
        $employees  = Employee::all();
        $leaveTypes = LeaveType::where('is_active', true)->get();

        foreach ($employees as $emp) {
            foreach ($leaveTypes as $type) {
                LeaveAllocation::updateOrCreate(
                    [
                        'employee_id'   => $emp->id,
                        'leave_type_id' => $type->id,
                        'year'          => $year,
                    ],
                    [
                        'days_allocated' => $type->default_days ?? 0,
                        'days_used'      => 0,
                    ]
                );
            }
        }
    }
}
