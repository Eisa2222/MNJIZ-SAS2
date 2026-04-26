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

        // Phase 8 — observability + security on every request.
        \App\Http\Middleware\RequestIdMiddleware::class,
        \App\Http\Middleware\SecureHeadersMiddleware::class,
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
            // Hotfix: re-binds TenantContext to the authenticated user's
            // tenant for path-less routes (/employees/*, /dashboard, etc).
            // No-op for /t/{slug}/* (URL-authoritative), admin guards, or
            // unauthenticated requests. See class doc-block for the full
            // root-cause + safety analysis.
            \App\Http\Middleware\ResolveTenantFromAuthenticatedUser::class,
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
        | Phase A — Subdomain + Custom Domain Resolution
        |----------------------------------------------------------------------
        | 'tenant.init.host'        → resolves tenant from HOST first
        |                             (custom domain → subdomain → path → header).
        |                             Use on the new subdomain/custom-domain
        |                             route group while `tenant.init` remains
        |                             on the legacy `/t/{slug}/...` group.
        |
        | 'tenant.prevent.central' → 404 if a tenant route is hit from one of
        |                             config('tenancy.central_domains'). Apply
        |                             alongside `tenant.init.host` on host-aware
        |                             tenant groups.
        */
        'tenant.init.host'        => \App\Http\Middleware\InitializeTenantByDomainOrSubdomain::class,
        'tenant.prevent.central'  => \App\Http\Middleware\PreventAccessFromCentralDomains::class,

        /*
        |----------------------------------------------------------------------
        | Super Admin (Phase 3)
        |----------------------------------------------------------------------
        */
        'admin.guest'       => \App\Http\Middleware\RedirectIfAuthenticatedAdmin::class,
        'admin.role'        => \App\Http\Middleware\RequireAdminRole::class,

        /*
        |----------------------------------------------------------------------
        | Phase B — Super Admin Compatibility Layer
        |----------------------------------------------------------------------
        | super-admin.guest    → RedirectIfAuthenticated for the new guard
        | admin.legacy.redirect → optional 301 from /admin/* to /super-admin/*
        |                          (off unless ADMIN_LEGACY_REDIRECT=true)
        |
        | The existing `admin.role` alias stays unchanged — it reads role
        | constants off the Admin model, and SuperAdmin extends Admin so
        | the same checks apply when invoked under the super_admin guard.
        */
        'super-admin.guest'      => \App\Http\Middleware\RedirectIfAuthenticatedSuperAdmin::class,
        'admin.legacy.redirect'  => \App\Http\Middleware\RedirectAdminToSuperAdmin::class,

        /*
        |----------------------------------------------------------------------
        | Phase C — System Settings runtime config
        |----------------------------------------------------------------------
        | apply.system_settings → reads SystemSetting::allAsKeyValue() and
        |                         Config::set()s mail / moyasar runtime keys.
        |                         Mounted on the central admin + super-admin
        |                         route groups (TenancyServiceProvider). Silently
        |                         no-ops when the table is missing.
        */
        'apply.system_settings'  => \App\Http\Middleware\ApplySystemSettings::class,

        /*
        | check.subscription   → Phase H. Gates tenant routes by subscription
        |                         state: trialing/active pass through; expired/
        |                         canceled/paused redirect to marketing.pricing;
        |                         past_due redirects to tenant.billing.index;
        |                         suspended renders tenant.suspended (HTTP 403).
        |                         Exempts billing portal, checkout, password
        |                         setup, auth, webhooks, health by route name.
        */
        'check.subscription'     => \App\Http\Middleware\CheckSubscription::class,

        'impersonating'     => \App\Http\Middleware\ImpersonationContext::class,
    ];

    protected $routeMiddleware = [
        // Middleware الأخرى...
        'ensure.microsoft.linked' => \App\Http\Middleware\EnsureMicrosoftLinked::class,

        'microsoft.token' => \App\Http\Middleware\CheckMicrosoftToken::class,
    ];
}
