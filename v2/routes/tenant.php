<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| V2 — TENANT routes (run on tenant subdomains + custom domains).
|
| Spec line 11: "tenant1.myapp.com OR tenant1.com" — both supported via
| InitializeTenancyByDomainOrSubdomain.
|
| Multi-DB: each request inside this group is automatically scoped to
| the tenant's own MySQL database via DatabaseTenancyBootstrapper.
|--------------------------------------------------------------------------
*/

Route::middleware([
    'web',
    InitializeTenancyByDomainOrSubdomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {

    // ─── Pre-auth (password setup link from welcome mail) ────────
    // Spec line 517: GET /password/setup/{token}, POST /password/setup
    Route::get('/password/setup/{token?}', function (\Illuminate\Http\Request $request) {
        return view('tenant.password-setup', [
            'token' => $request->route('token') ?? '',
            'email' => $request->query('email', ''),
        ]);
    })->middleware('signed')->name('tenant.password.setup');

    Route::post('/password/setup', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'email'                 => ['required', 'email'],
            'token'                 => ['required', 'string'],
            'password'              => ['required', 'confirmed', 'min:8'],
        ]);
        // (full implementation in next iteration: validate token, set
        //  password, redirect to login). See spec lines 339-342.
        return back()->with('status', 'password-setup placeholder');
    })->middleware('throttle:6,1')->name('tenant.password.setup.store');

    // ─── Tenant authenticated app (gated by CheckSubscription) ──
    Route::middleware([\App\Http\Middleware\CheckSubscription::class])->group(function () {
        Route::get('/', function () {
            $t = tenant();
            return view('tenant.dashboard', ['tenant' => $t]);
        })->name('tenant.home');

        // Real tenant features (cases, hearings, HR, AI, billing portal,
        // etc.) get added inside this group as the project evolves.
    });
});
