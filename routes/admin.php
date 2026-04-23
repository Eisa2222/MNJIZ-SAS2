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

    // Tenants
    Route::prefix('tenants')->name('admin.tenants.')->group(function () {
        Route::get('/',                         [TenantController::class, 'index'])->name('index');
        Route::get('/create',                   [TenantController::class, 'create'])->name('create');
        Route::post('/',                        [TenantController::class, 'store'])->name('store');
        Route::get('/{tenant:slug}',            [TenantController::class, 'show'])->name('show');
        Route::get('/{tenant:slug}/edit',       [TenantController::class, 'edit'])->name('edit');
        Route::put('/{tenant:slug}',            [TenantController::class, 'update'])->name('update');
        Route::post('/{tenant:slug}/suspend',   [TenantController::class, 'suspend'])->name('suspend');
        Route::post('/{tenant:slug}/activate',  [TenantController::class, 'activate'])->name('activate');

        // Impersonation entry point — an admin chooses a user within a tenant.
        Route::post('/{tenant:slug}/impersonate/{userId}', [ImpersonationController::class, 'start'])
            ->whereNumber('userId')
            ->name('impersonate');
    });

    // Central Settings
    Route::prefix('settings')->name('admin.settings.')->group(function () {
        Route::get('/',        [CentralSettingController::class, 'index'])->name('index');
        Route::put('/{key}',   [CentralSettingController::class, 'update'])->name('update');
    });

    // Subscriptions (Phase 5)
    Route::prefix('subscriptions')->name('admin.subscriptions.')->group(function () {
        Route::get('/',             [\App\Http\Controllers\Admin\SubscriptionController::class, 'index'])->name('index');
        Route::get('/{id}',         [\App\Http\Controllers\Admin\SubscriptionController::class, 'show'])->whereNumber('id')->name('show');
        Route::post('/{id}/cancel', [\App\Http\Controllers\Admin\SubscriptionController::class, 'cancel'])->whereNumber('id')->name('cancel');
        Route::post('/{id}/resume', [\App\Http\Controllers\Admin\SubscriptionController::class, 'resume'])->whereNumber('id')->name('resume');
    });

    // Coupons (Phase 5)
    Route::resource('coupons', \App\Http\Controllers\Admin\CouponController::class)
        ->except(['show'])
        ->names('admin.coupons');
});

// --- Impersonation stop (accessible from tenant UI while impersonating) ---
Route::post('/impersonation/stop', [ImpersonationController::class, 'stop'])
    ->name('admin.impersonation.stop');
