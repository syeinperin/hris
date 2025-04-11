<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use Carbon\Carbon;

class TestPayrollCalculation extends Command
{
    protected $signature = 'test:payroll';
    protected $description = 'Test payroll calculation for employees';

    public function handle()
    {
        $start_date = Carbon::now()->startOfMonth();
        $end_date   = Carbon::now()->endOfMonth();

        $employees = Employee::with([
            'designation',
            'attendances' => function ($query) use ($start_date, $end_date) {
                $query->whereBetween('time_in', [$start_date, $end_date]);
            }
        ])->get();

        foreach ($employees as $employee) {
            $ratePerMinute = $employee->designation->rate_per_minute ?? 0;
            $totalSeconds = 0;

            foreach ($employee->attendances as $attendance) {
                if ($attendance->time_in && $attendance->time_out) {
                    $timeIn  = Carbon::parse($attendance->time_in);
                    $timeOut = Carbon::parse($attendance->time_out);

                    if ($timeOut->lessThan($timeIn)) {
                        $timeOut->addDay();
                    }

                    $diff = $timeOut->diffInSeconds($timeIn);
                    $totalSeconds += $diff;
                }
            }

            $totalMinutes = round($totalSeconds / 60, 2);
            $grossPay     = $ratePerMinute * $totalMinutes;

            $this->info("Employee: {$employee->name}");
            $this->info("Rate per Minute: {$ratePerMinute}");
            $this->info("Total Minutes Worked: {$totalMinutes}");
            $this->info("Gross Pay: {$grossPay}");
            $this->line(str_repeat('-', 30));
        }

        return Command::SUCCESS;
    }
}
