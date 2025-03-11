<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('meters:email')
    ->dailyAt('07:30');
Schedule::command('meters:sync')
    ->dailyAt('00:00');
Schedule::command('meters:sync')
    ->dailyAt('12:00');

Schedule::command('meters:fill')->hourly();
Schedule::command('buildings:fill')->hourly();


