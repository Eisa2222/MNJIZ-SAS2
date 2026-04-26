<?php

declare(strict_types=1);

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\SuperAdmin\Auth\LoginController as SuperAdminLoginController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| V2 — CENTRAL routes (run on central domains only — never on tenant
| subdomains/custom domains). Multi-DB tenancy: these all hit the
| `mnjiz_v2_central` database.
|--------------------------------------------------------------------------
*/

// ─── Public marketing surface ────────────────────────────────────────
Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/landing', [LandingController::class, 'index'])->name('landing');

// ─── Public checkout flow (spec lines 181-192) ──────────────────────
Route::controller(CheckoutController::class)->group(function () {
    // Static routes BEFORE the /{plan} catch-all.
    Route::post('/checkout/apply-coupon', 'applyCoupon')
        ->middleware('throttle:30,1')
        ->name('checkout.apply-coupon');
    Route::get('/checkout/callback', 'callback')->name('checkout.callback');
    Route::get('/checkout/success',  'success')->name('checkout.success');
    Route::get('/checkout/failure',  'failure')->name('checkout.failure');
    Route::get('/checkout/{plan}',   'show')->name('checkout.show');
});

// ─── Super Admin (spec line 213: /super-admin) ──────────────────────
Route::prefix('super-admin')->name('super-admin.')->group(function () {

    // Guest auth
    Route::middleware('guest:super_admin')->group(function () {
        Route::get('login',  [SuperAdminLoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [SuperAdminLoginController::class, 'login'])
            ->middleware('throttle:5,1')->name('login.attempt');
    });

    // Authenticated panel
    Route::middleware('auth:super_admin')->group(function () {
        Route::post('logout', [SuperAdminLoginController::class, 'logout'])->name('logout');
        Route::get('/',          [SuperAdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [SuperAdminDashboardController::class, 'index']);

        // CRUD modules — scaffolding follows in next session.
        // Route::resource('tenants',       SuperAdmin\TenantController::class);
        // Route::resource('plans',         SuperAdmin\PlanController::class);
        // Route::resource('subscriptions', SuperAdmin\SubscriptionController::class);
        // Route::resource('payments',      SuperAdmin\PaymentController::class);
        // Route::resource('coupons',       SuperAdmin\CouponController::class);
        // Route::resource('landing/features', SuperAdmin\LandingFeatureController::class);
        // Route::resource('landing/faqs',     SuperAdmin\LandingFaqController::class);
        // Route::get('settings',  [SuperAdmin\SettingController::class, 'index'])->name('settings.index');
        // Route::put('settings',  [SuperAdmin\SettingController::class, 'update'])->name('settings.update');
    });
});

// ─── Moyasar webhook (no CSRF) ───────────────────────────────────────
Route::post('/webhooks/moyasar', function (\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Log::info('moyasar.webhook', $request->all());
    return response()->json(['ok' => true]);
})->name('webhooks.moyasar');
