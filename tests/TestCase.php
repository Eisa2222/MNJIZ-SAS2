<?php

namespace Tests;

use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Cache;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Every test starts from a clean tenancy slate.
     *
     * TenantContext holds the current tenant in a static property (shared
     * across the whole PHP process). Between two RefreshDatabase tests, the
     * tables are wiped but the static still points at a deleted Tenant
     * instance → any BelongsToTenant::creating() hook fills tenant_id with
     * a stale id → FK violation on insert.
     *
     * Clearing both the context and the cache at the start of each test
     * guarantees clean isolation without requiring every test class to
     * remember to do it itself.
     */
    protected function setUp(): void
    {
        parent::setUp();

        TenantContext::forget();
        Cache::flush();
    }

    protected function tearDown(): void
    {
        TenantContext::forget();

        parent::tearDown();
    }
}
