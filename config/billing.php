<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    */
    'currency' => env('BILLING_CURRENCY', 'SAR'),

    /*
    |--------------------------------------------------------------------------
    | Default Plan
    |--------------------------------------------------------------------------
    |
    | Tenants that are created without an explicit plan (e.g. self-signup,
    | DefaultTenantSeeder) get assigned this plan by TenantObserver.
    |
    */
    'default_plan_slug' => env('BILLING_DEFAULT_PLAN_SLUG', 'free'),

    /*
    |--------------------------------------------------------------------------
    | Unlimited Sentinel
    |--------------------------------------------------------------------------
    |
    | Stored in plan_features.value when a limit/metered feature is unlimited.
    | Callers can check ->unlimited on LimitCheckResult; this constant is
    | only used internally by the cast layer.
    |
    */
    'unlimited_sentinel' => '__unlimited__',

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Resolved feature map is cached per tenant for 10 minutes. Invalidated
    | automatically by PlanObserver and AssignPlanToTenantAction.
    |
    */
    'cache' => [
        'ttl_seconds'       => 600,
        'tenant_key_format' => 'tenant_%d_plan_features',
    ],

    /*
    |--------------------------------------------------------------------------
    | Enforcement
    |--------------------------------------------------------------------------
    */
    'enforcement' => [
        // Throw an exception vs. returning false when a feature is missing.
        'throw_on_missing_feature'     => false,
        'throw_on_limit_exceeded'      => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription Grace Period (Phase 5)
    |--------------------------------------------------------------------------
    | Days a past_due subscription keeps access before expiring. Default 3.
    */
    'grace_days' => (int) env('BILLING_GRACE_DAYS', 3),

    /*
    |--------------------------------------------------------------------------
    | Default Billing Cycle (Phase 5)
    |--------------------------------------------------------------------------
    */
    'default_cycle' => env('BILLING_DEFAULT_CYCLE', 'monthly'), // monthly | yearly
];
