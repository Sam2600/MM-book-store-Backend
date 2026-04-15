<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // // On the 5th of each month at 08:00, auto-create pending payout records
        // // for all authors whose earnings were calculated for the previous month.
        // // Earnings must be calculated first via POST /admin/earnings/calculate.
        // $schedule->command('payouts:bulk-create')
        //          ->monthlyOn(5, '08:00')
        //          ->withoutOverlapping()
        //          ->appendOutputTo(storage_path('logs/payouts-scheduler.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
