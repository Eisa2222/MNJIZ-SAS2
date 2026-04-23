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
    | Phase A: path-based (/t/{tenant}/...).
    | Phase B (later): subdomain ({tenant}.mnjiz.sa) + custom domain.
    |
    */
    'identification' => [
        'driver' => env('TENANCY_DRIVER', 'path'),
        'path' => [
            'prefix' => 't',
            'parameter' => 'tenant',
        ],
        'header' => 'X-Tenant-Slug',
    ],

    /*
    |--------------------------------------------------------------------------
    | Central Domains / Paths
    |--------------------------------------------------------------------------
    |
    | Paths and hosts that should NEVER be resolved as a tenant. The Super
    | Admin panel, billing webhooks, public landing pages, etc.
    |
    */
    'central_paths' => [
        'admin',
        'super-admin',
        'webhooks',
        'billing',
    ],

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
