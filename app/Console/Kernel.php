<?php

namespace App\Console;

use App\Events\VaccinationRemainder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\SendVaccinationReminder::class,
    ];
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Schedule the vaccination reminder command to run daily at 9 PM
        $schedule->command('vaccination:remind')->dailyAt('21:00');

        // $schedule->command('inspire')->hourly();
        $schedule->command('queue:work')->everyMinute()->runInBackground()->withoutOverlapping();
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
