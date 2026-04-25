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

/*
|--------------------------------------------------------------------------
| Phase 9 — Public marketing + signup flow
|--------------------------------------------------------------------------
| Public marketing surface (root + pricing) and the GTM signup pipeline
| that creates a tenant + owner user + 14-day trial subscription on the
| Pro plan. All central, no tenant resolution required (these run BEFORE
| a tenant exists).
*/
Route::controller(\App\Http\Controllers\Marketing\LandingController::class)
    ->group(function () {
        Route::get('/',         'index')->name('marketing.landing');
        Route::get('/pricing',  'pricing')->name('marketing.pricing');
    });

Route::controller(\App\Http\Controllers\Auth\Signup\PublicSignupController::class)
    ->group(function () {
        Route::get('/register',  'show')
            ->name('register');

        Route::post('/register', 'store')
            ->middleware('throttle:login')   // brute-force guard from Phase 8
            ->name('register.store');
    });

Route::middleware('auth')->group(function () {
    Route::get('/onboarding/welcome',
        [\App\Http\Controllers\Onboarding\OnboardingController::class, 'welcome']
    )->name('onboarding.welcome');
});
