<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use Carbon\Carbon;

class DeactivateExpiredEmployees extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'employees:deactivate-expired';

    /**
     * The console command description.
     */
    protected $description = 'Deactivate employees whose contract end date has passed and disable their user accounts';

    public function handle()
    {
        $today = Carbon::today()->toDateString();

        $expired = Employee::with('user')
            ->where('status', 'active')
            ->whereDate('employment_end_date', '<=', $today)
            ->get();

        if ($expired->isEmpty()) {
            $this->info('No expired contracts found.');
            return 0;
        }

        foreach ($expired as $emp) {
            $emp->status = 'inactive';
            $emp->save();

            if ($emp->user) {
                $emp->user->status = 'inactive';
                $emp->user->save();
            }

            $this->info("Deactivated {$emp->employee_code}");
        }

        return 0;
    }
}
