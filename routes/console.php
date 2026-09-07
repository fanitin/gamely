<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('challenges:generate --days=30')
    ->dailyAt('23:59')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('sitemap:generate')
    ->weeklyOn(1, '03:00')
    ->withoutOverlapping()
    ->onOneServer();
