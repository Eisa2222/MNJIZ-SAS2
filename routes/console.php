<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
| NOTE: Laravel 10 uses app/Console/Kernel.php::schedule() for the
| `Schedule::command(...)` syntax (the standalone `Schedule` facade is
| a Laravel 11 addition). The Phase G trial lifecycle schedule lives
| there alongside the Phase 5 billing schedule.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
