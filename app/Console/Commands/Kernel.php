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
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // Once daily at 02:00 AM, run BOTH probation and contract notifications.
        $schedule->command('employees:notify-all')->dailyAt('02:00');
        
        // If you want to split them, you could do:
        // $schedule->command('employees:notify-all --probation')->dailyAt('02:00');
        // $schedule->command('employees:notify-all --contract --days=7')->dailyAt('08:00');
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
