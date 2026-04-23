<?php

declare(strict_types=1);

namespace Tests\Feature\Billing;

use App\Actions\Billing\AssignPlanToTenantAction;
use App\Actions\Billing\CheckFeatureLimitAction;
use App\Actions\Billing\TrackUsageAction;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantUsage;
use App\Tenancy\TenantContext;
use Carbon\Carbon;
use Database\Seeders\DefaultPlansSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UsageTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DefaultPlansSeeder::class);
    }

    public function test_track_usage_creates_row_and_increments(): void
    {
        $tenant = $this->tenantOn('starter');

        TenantContext::runAs($tenant, function () use ($tenant) {
            $track = app(TrackUsageAction::class);
            $r1 = $track('sms.messages', 3);
            $this->assertSame(3, $r1->used);

            $r2 = $track('sms.messages', 2);
            $this->assertSame(5, $r2->used);

            $row = TenantUsage::withoutTenancy()
                ->where('tenant_id', $tenant->id)
                ->where('feature_key', 'sms.messages')
                ->sole();
            $this->assertSame(5, $row->used);
            $this->assertNotNull($row->reset_at);
        });
    }

    public function test_metered_limit_check_reflects_tracked_usage(): void
    {
        $tenant = $this->tenantOn('starter'); // sms.messages = 500 / month

        TenantContext::runAs($tenant, function () {
            $track = app(TrackUsageAction::class);
            $check = app(CheckFeatureLimitAction::class);

            $track('sms.messages', 498);

            $r = $check('sms.messages', amount: 1);
            $this->assertTrue($r->allowed);
            $this->assertSame(498, $r->used);
            $this->assertSame(500, $r->limit);

            $track('sms.messages', 2);

            $r2 = $check('sms.messages', amount: 1);
            $this->assertFalse($r2->allowed);
        });
    }

    public function test_counter_resets_after_period_boundary(): void
    {
        $tenant = $this->tenantOn('starter');

        TenantContext::runAs($tenant, function () use ($tenant) {
            $track = app(TrackUsageAction::class);
            $track('sms.messages', 10);

            // Fast-forward past the reset boundary.
            Carbon::setTestNow(Carbon::now()->addMonths(2));

            $r = $track('sms.messages', 1);
            $this->assertSame(1, $r->used, 'Counter should reset at period boundary before applying the new amount.');

            Carbon::setTestNow(null);
        });
    }

    public function test_unlimited_metered_still_tracks_usage_for_analytics(): void
    {
        $tenant = $this->tenantOn('professional'); // legal_ai.calls = unlimited

        TenantContext::runAs($tenant, function () {
            app(TrackUsageAction::class)('legal_ai.calls', 7);

            $r = app(CheckFeatureLimitAction::class)('legal_ai.calls', amount: 1);
            $this->assertTrue($r->allowed);
            $this->assertTrue($r->unlimited);
            $this->assertSame(7, $r->used); // still visible for billing analytics
        });
    }

    private function tenantOn(string $slug): Tenant
    {
        $tenant = Tenant::create([
            'name' => 'Firm '.$slug, 'slug' => 'firm-'.$slug.'-'.rand(1000, 9999), 'status' => 'active',
        ]);
        app(AssignPlanToTenantAction::class)->execute($tenant, Plan::where('slug', $slug)->firstOrFail());

        return $tenant->fresh();
    }
}
