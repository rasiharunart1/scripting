<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('sensor:set-hysteresis')->dailyAt('01:05');

Schedule::command('sensor:export-daily')->dailyAt('01:00');