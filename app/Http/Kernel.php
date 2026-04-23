<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\CheckMaintenanceMode::class,
            \App\Http\Middleware\MustChangePassword::class,
            // Shares impersonation state to all views (no-op when not impersonating).
            \App\Http\Middleware\ImpersonationContext::class,
        ],

        'api' => [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class . ':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's middleware aliases.
     *
     * Aliases may be used to conveniently assign middleware to routes and groups.
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \App\Http\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,


        // هل الموظف نشط ام لا
        'active' => \App\Http\Middleware\EnsureAuthenticatedAndActive::class,

        // موافق على السياسات
        'policy.agreement' => \App\Http\Middleware\PolicyAgreementMiddleware::class,


        /*
        |----------------------------------------------------------------------
        | Multi-Tenancy (Phase 2)
        |----------------------------------------------------------------------
        | 'tenant.init'     → resolves tenant from path/header, sets TenantContext
        | 'tenant.required' → 403 if no tenant resolved (strict API endpoints)
        |
        | The `tenant` group (web + tenant.init) is applied automatically by
        | TenancyServiceProvider to routes/tenant.php.
        */
        'tenant.init'     => \App\Http\Middleware\InitializeTenantMiddleware::class,
        'tenant.required' => \App\Http\Middleware\EnsureTenantContext::class,

        /*
        |----------------------------------------------------------------------
        | Super Admin (Phase 3)
        |----------------------------------------------------------------------
        */
        'admin.guest'       => \App\Http\Middleware\RedirectIfAuthenticatedAdmin::class,
        'impersonating'     => \App\Http\Middleware\ImpersonationContext::class,
    ];

    protected $routeMiddleware = [
        // Middleware الأخرى...
        'ensure.microsoft.linked' => \App\Http\Middleware\EnsureMicrosoftLinked::class,

        'microsoft.token' => \App\Http\Middleware\CheckMicrosoftToken::class,
    ];
}
