<?php

namespace App\Providers;

use App\Tenancy\TenantContext;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = 'employees/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * Phase 8 — three named limiters:
     *   - "api"        : per user / IP, used by the default api route group
     *   - "tenant_api" : per tenant_id, stricter per-firm ceiling so one
     *                    noisy tenant cannot starve others of API capacity
     *   - "login"      : login brute-force guard (per email + IP)
     *
     * Every limiter pulls its numbers from config('security.rate_limits.*')
     * so ops can dial them without a redeploy.
     */
    protected function configureRateLimiting(): void
    {
        // Default API limiter — by authenticated user id or IP.
        RateLimiter::for('api', function (Request $request) {
            $cfg = config('security.rate_limits.api');
            return Limit::perMinutes(
                (int) ($cfg['decay_minutes'] ?? 1),
                (int) ($cfg['max_attempts']  ?? 60)
            )->by($request->user()?->id ?: $request->ip());
        });

        // Per-tenant API limiter. Key is prefixed with tenant_id so
        // tenant A's usage cannot affect tenant B's counter.
        RateLimiter::for('tenant_api', function (Request $request) {
            $cfg      = config('security.rate_limits.tenant_api');
            $tenantId = TenantContext::currentId() ?? 0;
            $who      = $request->user()?->id ?: $request->ip();

            return Limit::perMinutes(
                (int) ($cfg['decay_minutes'] ?? 1),
                (int) ($cfg['max_attempts']  ?? 600)
            )->by("tenant_{$tenantId}_api_{$who}");
        });

        // Login brute-force guard. Key is email + IP so a guessable
        // password on a public user account doesn't lock every user
        // behind the same NAT.
        RateLimiter::for('login', function (Request $request) {
            $cfg   = config('security.rate_limits.login');
            $email = (string) $request->input('email', '');

            return Limit::perMinutes(
                (int) ($cfg['decay_minutes'] ?? 15),
                (int) ($cfg['max_attempts']  ?? 5)
            )->by(mb_strtolower($email) . '|' . $request->ip());
        });
    }
}
