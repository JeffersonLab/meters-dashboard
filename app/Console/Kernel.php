<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('meters:sync')
            ->dailyAt('00:00');
        $schedule->command('meters:sync')
            ->dailyAt('12:00');

        $schedule->command('meters:fill')->hourly();
        $schedule->command('buildings:fill')->hourly();

        //Meters every 4 hours
        //$schedule->command('meters:fill')
        //    ->dailyAt('00:05');
        //$schedule->command('meters:fill')
        //    ->dailyAt('06:05');
        //$schedule->command('meters:fill')
        //    ->dailyAt('12:05');
        //$schedule->command('meters:fill')
        //    ->dailyAt('18:05');

        //New climate data is only going to be available once/day
        //$schedule->command('climate:fill')
        //            ->dailyAt('01:00');

        // Make sure that the email goes out after the meters
        // have been filled with preceding day's worth of data
        // or else there may be false alarms in the report.
        //$schedule->command('meters:email')
        //        ->dailyAt('03:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
