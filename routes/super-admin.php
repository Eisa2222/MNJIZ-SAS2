<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\CentralSettingController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GrowthMetricsController;
use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\SuperAdmin\Auth\LoginController as SuperAdminLoginController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Super Admin Routes (Phase B — Path C compatibility layer)
|--------------------------------------------------------------------------
|
| Loaded by TenancyServiceProvider under the prefix /super-admin with the
| `web` middleware group. These routes NEVER resolve a tenant — the path
| /super-admin is in config('tenancy.central_paths').
|
| Guard: 'super_admin' (configured in config/auth.php). Auth-gated by
| middleware('auth:super_admin') below.
|
| MIRRORS routes/admin.php route-for-route. Same controllers (the admin
| controllers are guard-agnostic — they just render data and rely on
| `auth()->user()` which Laravel resolves against whichever guard the
| middleware assigned). Only the LOGIN flow is forked into a dedicated
| SuperAdmin\Auth\LoginController so it talks to the new guard.
|
| Route names use the `super-admin.` prefix so URL generation can pick
| either the legacy `admin.foo` or the new `super-admin.foo` without
| collisions.
|
*/

// --- Guest (login) ---------------------------------------------------------
Route::middleware('super-admin.guest')->group(function () {
    Route::get('login',  [SuperAdminLoginController::class, 'showLoginForm'])
        ->name('super-admin.login');
    Route::post('login', [SuperAdminLoginController::class, 'login'])
        ->name('super-admin.login.attempt');
});

// --- Authenticated super-admin area ---------------------------------------
Route::middleware('auth:super_admin')->group(function () {
    Route::post('logout', [SuperAdminLoginController::class, 'logout'])
        ->name('super-admin.logout');

    Route::get('/',          [DashboardController::class, 'index'])->name('super-admin.dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('super-admin.dashboard.alt');

    // Growth metrics (Phase 9) — read-only JSON for ops dashboards.
    Route::get('/api/growth', [GrowthMetricsController::class, 'show'])
        ->name('super-admin.growth.metrics');

    // Tenants — reads for all roles; mutations restricted to super_admin role.
    Route::prefix('tenants')->name('super-admin.tenants.')->group(function () {
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

        Route::post('/{tenant:slug}/impersonate/{userId}', [ImpersonationController::class, 'start'])
            ->middleware('admin.role:super_admin,support')
            ->whereNumber('userId')
            ->name('impersonate');
    });

    // Central Settings — super_admin role only.
    // Phase C tabbed UI mirrored under /super-admin/settings.
    Route::prefix('settings')->name('super-admin.settings.')
        ->middleware(['admin.role:super_admin', 'apply.system_settings'])
        ->group(function () {
            Route::get('/',                [\App\Http\Controllers\Admin\SystemSettingController::class, 'index'])->name('index');
            Route::put('/',                [\App\Http\Controllers\Admin\SystemSettingController::class, 'update'])->name('update');
            Route::post('/test-mail',      [\App\Http\Controllers\Admin\SystemSettingController::class, 'testMail'])->name('test-mail');
            Route::post('/test-moyasar',   [\App\Http\Controllers\Admin\SystemSettingController::class, 'testMoyasar'])->name('test-moyasar');

            // Phase 3 single-key endpoint mirror.
            Route::put('/legacy/{key}',    [CentralSettingController::class, 'update'])->name('legacy.update');
        });

    // Phase D — Landing Content (Features + FAQs). super_admin only.
    Route::middleware('admin.role:super_admin')->group(function () {
        Route::prefix('landing-features')->name('super-admin.landing-features.')->group(function () {
            Route::get('/',                 [\App\Http\Controllers\Admin\LandingFeatureController::class, 'index'])->name('index');
            Route::get('/create',           [\App\Http\Controllers\Admin\LandingFeatureController::class, 'create'])->name('create');
            Route::post('/',                [\App\Http\Controllers\Admin\LandingFeatureController::class, 'store'])->name('store');
            Route::post('/sort',            [\App\Http\Controllers\Admin\LandingFeatureController::class, 'sort'])->name('sort');
            Route::get('/{landing_feature}/edit', [\App\Http\Controllers\Admin\LandingFeatureController::class, 'edit'])->whereNumber('landing_feature')->name('edit');
            Route::put('/{landing_feature}',      [\App\Http\Controllers\Admin\LandingFeatureController::class, 'update'])->whereNumber('landing_feature')->name('update');
            Route::delete('/{landing_feature}',   [\App\Http\Controllers\Admin\LandingFeatureController::class, 'destroy'])->whereNumber('landing_feature')->name('destroy');
        });

        Route::prefix('landing-faqs')->name('super-admin.landing-faqs.')->group(function () {
            Route::get('/',             [\App\Http\Controllers\Admin\LandingFaqController::class, 'index'])->name('index');
            Route::get('/create',       [\App\Http\Controllers\Admin\LandingFaqController::class, 'create'])->name('create');
            Route::post('/',            [\App\Http\Controllers\Admin\LandingFaqController::class, 'store'])->name('store');
            Route::post('/sort',        [\App\Http\Controllers\Admin\LandingFaqController::class, 'sort'])->name('sort');
            Route::get('/{landing_faq}/edit', [\App\Http\Controllers\Admin\LandingFaqController::class, 'edit'])->whereNumber('landing_faq')->name('edit');
            Route::put('/{landing_faq}',      [\App\Http\Controllers\Admin\LandingFaqController::class, 'update'])->whereNumber('landing_faq')->name('update');
            Route::delete('/{landing_faq}',   [\App\Http\Controllers\Admin\LandingFaqController::class, 'destroy'])->whereNumber('landing_faq')->name('destroy');
        });
    });

    // Subscriptions — read for all admins, mutation for super_admin role only.
    Route::prefix('subscriptions')->name('super-admin.subscriptions.')->group(function () {
        Route::get('/',      [SubscriptionController::class, 'index'])->name('index');
        Route::get('/{id}',  [SubscriptionController::class, 'show'])->whereNumber('id')->name('show');

        Route::middleware('admin.role:super_admin')->group(function () {
            Route::post('/{id}/cancel', [SubscriptionController::class, 'cancel'])->whereNumber('id')->name('cancel');
            Route::post('/{id}/resume', [SubscriptionController::class, 'resume'])->whereNumber('id')->name('resume');
        });
    });

    // Coupons — listing + show open to all admins, CRUD restricted to super_admin role.
    Route::get('coupons',              [CouponController::class, 'index'])->name('super-admin.coupons.index');
    Route::get('coupons/{coupon}',     [CouponController::class, 'show'])->whereNumber('coupon')->name('super-admin.coupons.show');

    Route::middleware('admin.role:super_admin')->group(function () {
        Route::get('coupons/create',         [CouponController::class, 'create'])->name('super-admin.coupons.create');
        Route::post('coupons',               [CouponController::class, 'store'])->name('super-admin.coupons.store');
        Route::get('coupons/{coupon}/edit',  [CouponController::class, 'edit'])->whereNumber('coupon')->name('super-admin.coupons.edit');
        Route::put('coupons/{coupon}',       [CouponController::class, 'update'])->whereNumber('coupon')->name('super-admin.coupons.update');
        Route::post('coupons/{coupon}/toggle', [CouponController::class, 'toggle'])->whereNumber('coupon')->name('super-admin.coupons.toggle');
        Route::delete('coupons/{coupon}',    [CouponController::class, 'destroy'])->whereNumber('coupon')->name('super-admin.coupons.destroy');
    });
});
