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
Route::prefix('billing')->name('tenant.billing.')->group(function () {
    Route::get('/',              [\App\Http\Controllers\Tenant\BillingController::class, 'index'])->name('index');
    Route::post('/cancel',       [\App\Http\Controllers\Tenant\BillingController::class, 'cancel'])->name('cancel');
    Route::post('/resume',       [\App\Http\Controllers\Tenant\BillingController::class, 'resume'])->name('resume');
    Route::post('/coupon',       [\App\Http\Controllers\Tenant\BillingController::class, 'applyCoupon'])->name('coupon.apply');
});
