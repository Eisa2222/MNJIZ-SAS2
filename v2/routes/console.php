<?php

declare(strict_types=1);

use App\Console\Commands\CheckTrialExpiry;
use App\Console\Commands\SendTrialWarnings;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| V2 — Scheduled commands (spec lines 384-386).
|--------------------------------------------------------------------------
*/

Schedule::command('saas:check-trial-expiry')
    ->dailyAt('00:00')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('saas:send-trial-warnings')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->onOneServer();
