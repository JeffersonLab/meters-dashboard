<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::command('meters:sync')
    ->dailyAt('00:00');
Schedule::command('meters:sync')
    ->dailyAt('12:00');

Schedule::command('meters:fill')->hourly();
Schedule::command('buildings:fill')->hourly();

//Meters every 4 hours
//Schedule::command('meters:fill')
//    ->dailyAt('00:05');
//Schedule::command('meters:fill')
//    ->dailyAt('06:05');
//Schedule::command('meters:fill')
//    ->dailyAt('12:05');
//Schedule::command('meters:fill')
//    ->dailyAt('18:05');

//New climate data is only going to be available once/day
//Schedule::command('climate:fill')
//            ->dailyAt('01:00');

// Make sure that the email goes out after the meters
// have been filled with preceding day's worth of data
// or else there may be false alarms in the report.
//Schedule::command('meters:email')
//        ->dailyAt('03:00');
