<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\CentralSettingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\Admin\TenantController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin (Super Admin) Routes
|--------------------------------------------------------------------------
|
| Loaded by TenancyServiceProvider under prefix /admin with the `web`
| middleware group. These routes NEVER resolve a tenant and NEVER apply
| tenant.init (the path /admin is in config('tenancy.central_paths')).
|
| Guard: 'admin' (configured in config/auth.php). Auth-gated by
| middleware('auth:admin') below.
|
*/

// --- Guest (login) ---------------------------------------------------------
Route::middleware('admin.guest')->group(function () {
    Route::get('login',  [LoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('login', [LoginController::class, 'login'])->name('admin.login.attempt');
});

// --- Authenticated admin area ---------------------------------------------
Route::middleware('auth:admin')->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('admin.logout');

    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard.alt');

    // Phase 9 — GTM growth metrics endpoint (read-only JSON).
    Route::get('/api/growth',
        [\App\Http\Controllers\Admin\GrowthMetricsController::class, 'show']
    )->name('admin.growth.metrics');

    // Tenants — reads for all admin roles; mutations restricted to super_admin.
    Route::prefix('tenants')->name('admin.tenants.')->group(function () {
        Route::get('/',                         [TenantController::class, 'index'])->name('index');
        Route::get('/{tenant:slug}',            [TenantController::class, 'show'])->name('show');

        Route::middleware('admin.role:super_admin')->group(function () {
            Route::get('/create',                   [TenantController::class, 'create'])->name('create');
            Route::post('/',                        [TenantController::class, 'store'])->name('store');
            Route::get('/{tenant:slug}/edit',       [TenantController::class, 'edit'])->name('edit');
            Route::put('/{tenant:slug}',            [TenantController::class, 'update'])->name('update');
            Route::post('/{tenant:slug}/suspend',   [TenantController::class, 'suspend'])->name('suspend');
            Route::post('/{tenant:slug}/activate',  [TenantController::class, 'activate'])->name('activate');
        });

        // Impersonation: super_admin + support roles only (matches Admin::canImpersonate).
        Route::post('/{tenant:slug}/impersonate/{userId}', [ImpersonationController::class, 'start'])
            ->middleware('admin.role:super_admin,support')
            ->whereNumber('userId')
            ->name('impersonate');
    });

    // Central Settings — super_admin only (platform-wide configuration).
    // Phase C — `/admin/settings` now serves the new tabbed UI from
    // SystemSettingController. The Phase 3 single-key Central endpoint
    // is retained as `admin.settings.legacy.update` for back-compat.
    Route::prefix('settings')->name('admin.settings.')
        ->middleware(['admin.role:super_admin', 'apply.system_settings'])
        ->group(function () {
            Route::get('/',                [\App\Http\Controllers\Admin\SystemSettingController::class, 'index'])->name('index');
            Route::put('/',                [\App\Http\Controllers\Admin\SystemSettingController::class, 'update'])->name('update');
            Route::post('/test-mail',      [\App\Http\Controllers\Admin\SystemSettingController::class, 'testMail'])->name('test-mail');
            Route::post('/test-moyasar',   [\App\Http\Controllers\Admin\SystemSettingController::class, 'testMoyasar'])->name('test-moyasar');

            // Phase 3 single-key endpoint, retained for back-compat.
            Route::put('/legacy/{key}',    [CentralSettingController::class, 'update'])->name('legacy.update');
        });

    // Subscriptions — read for all admins, mutation for super_admin only.
    Route::prefix('subscriptions')->name('admin.subscriptions.')->group(function () {
        Route::get('/',             [\App\Http\Controllers\Admin\SubscriptionController::class, 'index'])->name('index');
        Route::get('/{id}',         [\App\Http\Controllers\Admin\SubscriptionController::class, 'show'])->whereNumber('id')->name('show');

        Route::middleware('admin.role:super_admin')->group(function () {
            Route::post('/{id}/cancel', [\App\Http\Controllers\Admin\SubscriptionController::class, 'cancel'])->whereNumber('id')->name('cancel');
            Route::post('/{id}/resume', [\App\Http\Controllers\Admin\SubscriptionController::class, 'resume'])->whereNumber('id')->name('resume');
        });
    });

    // Coupons — listing open to all admins, CRUD restricted to super_admin.
    Route::get('coupons', [\App\Http\Controllers\Admin\CouponController::class, 'index'])->name('admin.coupons.index');

    Route::middleware('admin.role:super_admin')->group(function () {
        Route::get('coupons/create',       [\App\Http\Controllers\Admin\CouponController::class, 'create'])->name('admin.coupons.create');
        Route::post('coupons',             [\App\Http\Controllers\Admin\CouponController::class, 'store'])->name('admin.coupons.store');
        Route::get('coupons/{coupon}/edit', [\App\Http\Controllers\Admin\CouponController::class, 'edit'])->name('admin.coupons.edit');
        Route::put('coupons/{coupon}',     [\App\Http\Controllers\Admin\CouponController::class, 'update'])->name('admin.coupons.update');
        Route::delete('coupons/{coupon}',  [\App\Http\Controllers\Admin\CouponController::class, 'destroy'])->name('admin.coupons.destroy');
    });
});

// --- Impersonation stop (accessible from tenant UI while impersonating) ---
Route::post('/impersonation/stop', [ImpersonationController::class, 'stop'])
    ->name('admin.impersonation.stop');
