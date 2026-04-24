<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Central (Super Admin) Routes
|--------------------------------------------------------------------------
|
| These routes are NEVER resolved as a tenant. They are loaded by
| TenancyServiceProvider under the `web` middleware group and are excluded
| from InitializeTenantMiddleware via config('tenancy.central_paths').
|
| Target users: SaaS platform operators (Super Admins, support, billing ops).
| Fully populated in Phase 3 (admin foundation) and Phase 5 (billing).
|
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware([
        // 'auth:admin',   // Added in Phase 3 when admin guard is configured
    ])
    ->group(function () {
        Route::get('/ping', fn () => response()->json([
            'pong'    => true,
            'layer'   => 'central',
            'message' => 'Super Admin panel scaffold — to be built in Phase 3.',
        ]))->name('ping');
    });

/*
|--------------------------------------------------------------------------
| Webhook / Public central endpoints (no auth)
|--------------------------------------------------------------------------
*/

Route::prefix('webhooks')
    ->name('webhooks.')
    ->group(function () {
        // Moyasar, Stripe, etc. land here — implemented in Phase 5.
    });

/*
|--------------------------------------------------------------------------
| Health / liveness / readiness endpoints (Phase 8)
|--------------------------------------------------------------------------
| No auth — consumed by load-balancers, container orchestrators, uptime
| monitors. Returns 200/503 JSON.
*/
Route::prefix('health')
    ->name('health.')
    ->controller(\App\Http\Controllers\Health\HealthController::class)
    ->group(function () {
        Route::get('/',       'overall')->name('overall');
        Route::get('/db',     'db')->name('db');
        Route::get('/queue',  'queue')->name('queue');
        Route::get('/cache',  'cache')->name('cache');
        Route::get('/ready',  'ready')->name('ready');
    });
