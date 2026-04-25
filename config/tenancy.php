<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Tenancy Mode
    |--------------------------------------------------------------------------
    |
    | MNJIZ SaaS runs in "single database" mode: all tenants share one DB,
    | isolation is enforced via a tenant_id column + a global scope on every
    | tenant-aware Eloquent model.
    |
    */
    'mode' => env('TENANCY_MODE', 'single_database'),

    /*
    |--------------------------------------------------------------------------
    | Tenant Model
    |--------------------------------------------------------------------------
    */
    'tenant_model' => App\Models\Tenant::class,

    /*
    |--------------------------------------------------------------------------
    | Default Tenant (Compatibility Layer)
    |--------------------------------------------------------------------------
    |
    | While the legacy routes under /employees/* are being migrated, any
    | request that reaches a tenant-aware model without a resolved tenant
    | will be attributed to this tenant. Set TENANCY_FALLBACK_ENABLED=false
    | in production once all traffic flows through /t/{tenant} or an admin
    | route.
    |
    */
    'fallback_enabled' => (bool) env('TENANCY_FALLBACK_ENABLED', true),
    'default_tenant_slug' => env('TENANCY_DEFAULT_TENANT_SLUG', 'default'),
    'default_tenant_id' => (int) env('TENANCY_DEFAULT_TENANT_ID', 1),

    /*
    |--------------------------------------------------------------------------
    | Tenant Identification
    |--------------------------------------------------------------------------
    |
    | Resolution order is fixed in TenantResolver:
    |   1. custom-domain   — exact host match in `domains` table
    |   2. subdomain       — `{slug}.{app_base_domain}` strip + lookup
    |   3. path-based      — /t/{slug}/...   (legacy, ALWAYS preserved)
    |   4. route param     — {tenant} bound by Laravel
    |   5. header          — X-Tenant-Slug
    |   6. default tenant  — when fallback_enabled
    |
    | The two boolean flags below let an operator turn off the host-based
    | branches without touching code (e.g. local dev where the host is
    | always `localhost`). The path-based branch cannot be turned off —
    | it's the back-compat shield for every Phase 2-9 route + test.
    |
    */
    'identification' => [
        'driver' => env('TENANCY_DRIVER', 'path'),
        'path' => [
            'prefix' => 't',
            'parameter' => 'tenant',
        ],
        'header' => 'X-Tenant-Slug',

        'subdomain' => [
            'enabled'         => (bool) env('TENANCY_SUBDOMAIN_ENABLED', true),
            'app_base_domain' => env('TENANCY_APP_BASE_DOMAIN', 'mnjiz.sa'),
        ],
        'custom_domain' => [
            'enabled' => (bool) env('TENANCY_CUSTOM_DOMAIN_ENABLED', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Central Domains / Paths
    |--------------------------------------------------------------------------
    |
    | Paths AND hosts that should NEVER be resolved as a tenant. The Super
    | Admin panel, billing webhooks, public landing pages, marketing site.
    |
    | central_paths    — first segment of the URL path (e.g. /admin/*)
    | central_domains  — exact HTTP host match (e.g. mnjiz.sa, www.mnjiz.sa)
    |
    | Both are checked: a request to `https://mnjiz.sa/t/acme/...` would NOT
    | resolve as a tenant because the host is central, even though the path
    | uses the `/t/` prefix.
    |
    */
    'central_paths' => [
        'admin',
        'super-admin',
        'webhooks',
        'billing',
    ],

    'central_domains' => array_filter([
        env('TENANCY_CENTRAL_DOMAIN_PRIMARY', 'mnjiz.sa'),
        env('TENANCY_CENTRAL_DOMAIN_WWW',     'www.mnjiz.sa'),
        env('TENANCY_CENTRAL_DOMAIN_APP',     'app.mnjiz.sa'),
    ]),

    /*
    |--------------------------------------------------------------------------
    | Phase B — Legacy /admin → /super-admin Redirect
    |--------------------------------------------------------------------------
    |
    | When `true`, every request hitting /admin/* gets a 301 redirect to
    | the spec-compliant /super-admin/* equivalent. When `false` (default),
    | /admin keeps responding directly — preserving 173+ existing tests
    | and any external links/bookmarks that target the legacy surface.
    |
    | Toggle via `ADMIN_LEGACY_REDIRECT=true` only after every admin user
    | has logged into /super-admin at least once and external integrations
    | have been updated.
    |
    */
    'admin_legacy_redirect' => (bool) env('ADMIN_LEGACY_REDIRECT', false),

    /*
    |--------------------------------------------------------------------------
    | Strict Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, querying a BelongsToTenant model with no resolved tenant
    | context throws. Keep this FALSE during Phase 2 so migrations, seeders
    | and console commands don't explode. Turn it on in Phase 6 per-env.
    |
    */
    'strict' => (bool) env('TENANCY_STRICT', false),

    /*
    |--------------------------------------------------------------------------
    | Cache / Queue / Storage Isolation
    |--------------------------------------------------------------------------
    */
    'isolation' => [
        'cache_prefix_format' => 'tenant_%d_', // tenant_<id>_
        'queue_tag_format' => 'tenant:%d',
        'storage_root' => 'tenants', // storage/app/tenants/{id}/...
    ],

    /*
    |--------------------------------------------------------------------------
    | Activity Log
    |--------------------------------------------------------------------------
    */
    'activity_log' => [
        'stamp_tenant_id' => true,
    ],
];
