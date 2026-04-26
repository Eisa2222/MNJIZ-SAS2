<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

/*
|--------------------------------------------------------------------------
| V2 — Laravel 13 application bootstrap.
|
| Note: in Laravel 11+ the old Kernel.php files are gone. Middleware,
| commands, exceptions are all configured here.
|
| `routes/tenant.php` is loaded by stancl/tenancy's TenancyServiceProvider
| automatically — we don't list it in withRouting().
|--------------------------------------------------------------------------
*/

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web:      __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health:   '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register middleware aliases so route files can use short names.
        $middleware->alias([
            'check.subscription' => \App\Http\Middleware\CheckSubscription::class,
        ]);

        // V2 spec doesn't require any global middleware additions today.
        // Add here as the project grows (e.g. ApplySystemSettings).
    })
    ->withProviders([
        App\Providers\TenancyServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
