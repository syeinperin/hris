<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Register Artisan commands.
     */
    protected $commands = [
        \App\Console\Commands\EmploymentNotifications::class,
        \App\Console\Commands\TestRoleBinding::class,

        // ← Add this line:
        \App\Console\Commands\DeactivateExpiredEmployees::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // Once daily at 02:00 AM, run your existing notifications.
        $schedule->command('employees:notify-all')->dailyAt('02:00');
        
        // New: run daily at midnight to auto‐deactivate expired contracts.
        $schedule->command('employees:deactivate-expired')->dailyAt('00:00');
    }

    /**
     * Register Closure‐based commands.
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
