<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant Routes (Phase 2 scaffold)
|--------------------------------------------------------------------------
|
| Loaded by TenancyServiceProvider with:
|     prefix:     /t/{tenant}
|     middleware: ['web', 'tenant.init']
|
| During Phase 2 the bulk of the application still lives in routes/web.php
| under the legacy /employees/* prefix. Those routes continue to work
| (backed by the Default Tenant via the compatibility layer).
|
| NEW routes scoped to a tenant should be registered here going forward.
| In Phase 6 we migrate the /employees/* routes into this file.
|
*/

Route::get('/ping', function () {
    $tenant = \App\Tenancy\TenantContext::current();

    return response()->json([
        'pong'        => true,
        'layer'       => 'tenant',
        'tenant_id'   => $tenant?->id,
        'tenant_slug' => $tenant?->slug,
        'tenant_name' => $tenant?->name,
    ]);
})->name('tenant.ping');

// ---- Tenant Billing Portal (Phase 5) ----
// Requires an authenticated tenant user (web guard). Unauthenticated visits
// redirect to /employees/login via our custom Authenticate middleware.
//
// Phase H: NOT gated by `check.subscription` — past-due / no-sub users
// MUST be able to reach this portal to fix their billing. The middleware's
// EXEMPT_ROUTE_PATTERNS list also names `tenant.billing.*` defensively.
Route::prefix('billing')
    ->name('tenant.billing.')
    ->middleware('auth')
    ->group(function () {
        Route::get('/',        [\App\Http\Controllers\Tenant\BillingController::class, 'index'])->name('index');
        Route::post('/cancel', [\App\Http\Controllers\Tenant\BillingController::class, 'cancel'])->name('cancel');
        Route::post('/resume', [\App\Http\Controllers\Tenant\BillingController::class, 'resume'])->name('resume');
        Route::post('/coupon', [\App\Http\Controllers\Tenant\BillingController::class, 'applyCoupon'])->name('coupon.apply');
    });

// ---- Suspended landing (Phase H) ----
// Reachable when CheckSubscription middleware encounters a tenant whose
// status is 'suspended'. Returns 403 + the dedicated suspended view so
// the user sees a branded explanation instead of a stack trace or a
// bare 403 page.
Route::get('/suspended', function () {
    $tenant = \App\Tenancy\TenantContext::current();
    abort_unless($tenant, 404);

    return response()->view('tenant.suspended', [
        'tenant'        => $tenant,
        'support_email' => (string) (\App\Models\SystemSetting::get('support_email') ?? 'support@mnjiz.sa'),
        'app_name'      => (string) (\App\Models\SystemSetting::get('app_name')      ?? 'MNJIZ'),
    ], 403);
})->name('tenant.suspended');

/*
|--------------------------------------------------------------------------
| Phase H — Subscription-gated tenant features
|--------------------------------------------------------------------------
| Real tenant features (legacy /employees/* still in routes/web.php) will
| move under this group as they're modernised. New routes added INSIDE
| this group inherit the subscription gate automatically; for now it's
| an empty container so the middleware is wired and ready.
|
| Note: tenant.ping, tenant.billing.*, and tenant.suspended above stay
| OUTSIDE this group on purpose — they're either pure infra (ping) or
| must remain reachable in degraded states (billing portal, suspended
| page itself).
*/
Route::middleware(['auth', 'check.subscription'])->group(function () {
    // Future: tenant feature routes go here.
});
