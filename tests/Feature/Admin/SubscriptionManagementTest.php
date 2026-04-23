<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Actions\Billing\Subscription\StartTrialAction;
use App\Enums\Billing\SubscriptionStatus;
use App\Models\Admin;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Database\Seeders\DefaultPlansSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class SubscriptionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DefaultPlansSeeder::class);
    }

    public function test_admin_index_renders_subscription_list(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin, 'admin');

        $this->scaffoldSubscription();

        $this->get('/admin/subscriptions')
            ->assertOk()
            ->assertSee('Subscriptions', false);
    }

    public function test_admin_show_renders_subscription_detail(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $sub = $this->scaffoldSubscription();

        $this->get("/admin/subscriptions/{$sub->id}")
            ->assertOk()
            ->assertSee('Subscription #'.$sub->id, false);
    }

    public function test_super_admin_can_cancel_subscription(): void
    {
        $this->actingAs($this->superAdmin(), 'admin');
        $sub = $this->scaffoldSubscription();

        $response = $this->post("/admin/subscriptions/{$sub->id}/cancel");
        $response->assertRedirect();

        $this->assertSame(SubscriptionStatus::Canceled, $sub->fresh()->status);
    }

    public function test_regular_admin_cannot_cancel_subscription(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $sub = $this->scaffoldSubscription();

        $response = $this->post("/admin/subscriptions/{$sub->id}/cancel");
        $response->assertStatus(403);

        $this->assertNotSame(SubscriptionStatus::Canceled, $sub->fresh()->status);
    }

    public function test_super_admin_can_resume_canceled_subscription(): void
    {
        $this->actingAs($this->superAdmin(), 'admin');
        $sub = $this->scaffoldSubscription();
        $sub->update(['status' => SubscriptionStatus::Canceled, 'canceled_at' => now()]);

        $this->post("/admin/subscriptions/{$sub->id}/resume")->assertRedirect();

        $this->assertSame(SubscriptionStatus::Active, $sub->fresh()->status);
    }

    public function test_non_existent_subscription_returns_404(): void
    {
        $this->actingAs($this->superAdmin(), 'admin');

        $this->get('/admin/subscriptions/999999')->assertStatus(404);
    }

    private function scaffoldSubscription(): Subscription
    {
        $tenant = Tenant::create(['name' => 'A', 'slug' => 'a-'.uniqid(), 'status' => 'active']);
        $plan   = Plan::where('slug', 'starter')->firstOrFail();

        return TenantContext::runAs($tenant, fn () => app(StartTrialAction::class)->execute($tenant, $plan));
    }

    private function superAdmin(): Admin
    {
        return Admin::create([
            'name' => 'S', 'email' => 's-'.uniqid().'@mnjiz.sa',
            'password' => Hash::make('x'), 'role' => Admin::ROLE_SUPER_ADMIN,
            'status' => Admin::STATUS_ACTIVE,
        ]);
    }

    private function admin(): Admin
    {
        return Admin::create([
            'name' => 'A', 'email' => 'a-'.uniqid().'@mnjiz.sa',
            'password' => Hash::make('x'), 'role' => Admin::ROLE_ADMIN,
            'status' => Admin::STATUS_ACTIVE,
        ]);
    }
}
