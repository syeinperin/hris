<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\ContractEndingNotification;
use App\Mail\ProbationRegularizedNotification;

class EmploymentNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * We accept these two options:
     *   --probation : run the probation‐regularization logic
     *   --contract  : run the contract‐ending notification logic
     *
     * If neither flag is provided, we do **both** by default.
     *
     * You can also pass --days=7 (integer) to control how many days in advance
     * you want the “upcoming contract end” notifications. Default is 7.
     */
    protected $signature = 'employees:notify-all
                            {--probation : Only run probation‐regularization}
                            {--contract : Only run contract‐ending notification}
                            {--days=7 : How many days in advance to notify for contract end}';

    /**
     * The console command description.
     */
    protected $description = 'Run probation‐to‐regular transitions AND/or contract‐end notifications for all employment types.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        $runProbation = $this->option('probation');
        $runContract  = $this->option('contract');
        $days         = (int)$this->option('days');

        // If no flags provided, do both.
        if (! $runProbation && ! $runContract) {
            $runProbation = true;
            $runContract  = true;
        }

        // ───────────────────────────────────────────────────────────────────────
        // PART 1: Probation → Regular transitions
        // ───────────────────────────────────────────────────────────────────────
        if ($runProbation) {
            $this->info('► Checking for probationary employees to regularize...');
            $probEmployees = Employee::where('employment_type', 'probationary')
                                      ->whereDate('employment_end_date', '<=', $today)
                                      ->get();

            if ($probEmployees->isEmpty()) {
                $this->info('No probationary employees to regularize today.');
            } else {
                foreach ($probEmployees as $emp) {
                    $oldType = $emp->employment_type;
                    $emp->employment_type = 'regular';
                    $emp->save();

                    // (Optional) send a “you have been regularized” email to the employee
                    try {
                        Mail::to($emp->user->email)
                            ->send(new ProbationRegularizedNotification($emp));
                        $this->info("Regularized {$emp->name} (ID {$emp->id}) and emailed them.");
                    } catch (\Exception $e) {
                        $this->error("Regularized {$emp->name} but failed to send email: {$e->getMessage()}");
                        Log::error("ProbationRegularize email failed for Employee ID {$emp->id}: {$e->getMessage()}");
                    }
                }
            }
        }

        // ───────────────────────────────────────────────────────────────────────
        // PART 2: Contract‐Ending Notifications for All Types
        // ───────────────────────────────────────────────────────────────────────
        if ($runContract) {
            $this->info("► Checking for employees whose contract ends within {$days} days or has already expired...");

            // Employees ending within next $days days inclusive
            $upcoming = Employee::whereDate('employment_end_date', '>=', $today)
                                ->whereDate('employment_end_date', '<=', $today->copy()->addDays($days))
                                ->get();

            // Employees whose end date < today
            $expired = Employee::whereDate('employment_end_date', '<', $today)
                                ->get();

            if ($upcoming->isEmpty() && $expired->isEmpty()) {
                $this->info("No employees with contracts ending within {$days} days, and none expired.");
            } else {
                $mailData = [
                    'upcoming' => $upcoming,
                    'expired'  => $expired,
                    'days'     => $days,
                    'today'    => $today->toDateString(),
                ];

                // HR email address (configure in .env or default to hr@example.com)
                $hrEmail = config('mail.hr_notification_address', 'hr@example.com');

                try {
                    Mail::to($hrEmail)
                        ->send(new ContractEndingNotification($mailData));
                    $this->info("Sent contract‐ending notification to {$hrEmail}.");
                } catch (\Exception $e) {
                    $this->error("Failed to send contract‐ending notification email: {$e->getMessage()}");
                    Log::error("ContractEndingNotification email failed: {$e->getMessage()}");
                }
            }
        }

        $this->info('► EmploymentNotifications command finished.');
        return 0;
    }
}
