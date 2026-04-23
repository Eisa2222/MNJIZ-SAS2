<?php

declare(strict_types=1);

namespace Tests\Feature\Tenancy;

use App\Models\Tenant;
use App\Tenancy\Support\TenantStorage;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * TenantStorage must guarantee that every resolved path is prefixed with
 * tenants/{tenant_id}/... so a bug in one tenant's code can never read or
 * write another tenant's files. This is a structural security property.
 */
final class TenantStorageTest extends TestCase
{
    use RefreshDatabase;

    public function test_path_is_prefixed_with_tenant_id(): void
    {
        $tenant = Tenant::create(['name' => 'S', 'slug' => 's-'.uniqid(), 'status' => 'active']);

        TenantContext::runAs($tenant, function () use ($tenant) {
            $resolved = TenantStorage::path('lawsuits/42/contract.pdf');

            $this->assertSame("tenants/{$tenant->id}/lawsuits/42/contract.pdf", $resolved);
        });
    }

    public function test_path_throws_without_tenant_when_fallback_disabled(): void
    {
        config(['tenancy.fallback_enabled' => false]);
        TenantContext::forget();

        $this->expectException(\RuntimeException::class);

        TenantStorage::path('x.pdf');
    }

    public function test_disk_adapter_writes_under_tenant_prefix(): void
    {
        Storage::fake('tenants');

        $tenant = Tenant::create(['name' => 'W', 'slug' => 'w-'.uniqid(), 'status' => 'active']);

        TenantContext::runAs($tenant, function () use ($tenant) {
            TenantStorage::disk('tenants')->put('docs/a.txt', 'hello');

            // Raw underlying disk should have the prefixed path.
            Storage::disk('tenants')->assertExists("tenants/{$tenant->id}/docs/a.txt");

            // A path WITHOUT the prefix should NOT exist.
            Storage::disk('tenants')->assertMissing('docs/a.txt');
        });
    }

    public function test_tenant_a_cannot_read_tenant_b_files_through_adapter(): void
    {
        Storage::fake('tenants');

        $a = Tenant::create(['name' => 'A', 'slug' => 'a-'.uniqid(), 'status' => 'active']);
        $b = Tenant::create(['name' => 'B', 'slug' => 'b-'.uniqid(), 'status' => 'active']);

        TenantContext::runAs($a, fn () => TenantStorage::disk('tenants')->put('secret.txt', 'A secret'));

        TenantContext::runAs($b, function () {
            $this->assertFalse(
                TenantStorage::disk('tenants')->exists('secret.txt'),
                'Tenant B must not see tenant A files at the same logical path.'
            );
        });
    }
}
