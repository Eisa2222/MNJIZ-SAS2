<?php

declare(strict_types=1);

namespace Tests\Feature\Tenant;

use App\Enums\Billing\BillingCycle;
use App\Enums\Billing\PaymentGateway;
use App\Enums\Billing\SubscriptionStatus;
use App\Http\Middleware\CheckSubscription;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Phase H — CheckSubscription middleware regression suite.
 *
 *   1. active subscription → access allowed
 *   2. trial subscription → access allowed
 *   3. expired subscription → redirected to /pricing
 *   4. past_due → redirected to billing portal
 *   5. suspended tenant → 403 with suspended view
 *   6. no subscription → redirected to /pricing
 *   7. excluded routes (billing, checkout, password setup) bypass
 *   8. central routes (no tenant context) not affected
 *   9. no 500 errors in any state — every code path returns a Response
 */
final class CheckSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        TenantContext::forget();   // start each test with a clean slate
    }

    protected function tearDown(): void
    {
        TenantContext::forget();
        parent::tearDown();
    }

    // 1 ─────────────────────────────────────────────────────────────────
    public function test_active_subscription_allows_access(): void
    {
        [$tenant, $user, $sub] = $this->seedTenant(SubscriptionStatus::Active);
        TenantContext::set($tenant);

        $response = $this->runMiddleware($this->makeRequestForRoute('tenant.feature.demo'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getContent());
    }

    // 2 ─────────────────────────────────────────────────────────────────
    public function test_trialing_subscription_allows_access(): void
    {
        [$tenant, $user, $sub] = $this->seedTenant(SubscriptionStatus::Trialing);
        TenantContext::set($tenant);

        $response = $this->runMiddleware($this->makeRequestForRoute('tenant.feature.demo'));

        $this->assertSame(200, $response->getStatusCode());
    }

    // 3 ─────────────────────────────────────────────────────────────────
    public function test_expired_subscription_redirects_to_pricing(): void
    {
        [$tenant, $user, $sub] = $this->seedTenant(SubscriptionStatus::Expired);
        TenantContext::set($tenant);

        $response = $this->runMiddleware($this->makeRequestForRoute('tenant.feature.demo'));

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('/pricing', (string) $response->headers->get('Location'));
    }

    // 4 ─────────────────────────────────────────────────────────────────
    public function test_past_due_redirects_to_billing_portal(): void
    {
        [$tenant, $user, $sub] = $this->seedTenant(SubscriptionStatus::PastDue);
        TenantContext::set($tenant);

        $response = $this->runMiddleware($this->makeRequestForRoute('tenant.feature.demo'));

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('/billing', (string) $response->headers->get('Location'));
    }

    // 5 ─────────────────────────────────────────────────────────────────
    public function test_suspended_tenant_renders_suspended_view(): void
    {
        [$tenant, $user, $sub] = $this->seedTenant(SubscriptionStatus::Active);
        $tenant->update(['status' => Tenant::STATUS_SUSPENDED]);
        TenantContext::set($tenant->fresh());

        $response = $this->runMiddleware($this->makeRequestForRoute('tenant.feature.demo'));

        $this->assertSame(403, $response->getStatusCode());
        $this->assertStringContainsString(__('subscription.suspended.heading'), (string) $response->getContent());
    }

    // 6 ─────────────────────────────────────────────────────────────────
    public function test_no_subscription_redirects_to_pricing(): void
    {
        $tenant = Tenant::create([
            'name'   => 'Empty Co',
            'slug'   => 'empty-'.uniqid(),
            'status' => Tenant::STATUS_ACTIVE,
        ]);
        TenantContext::set($tenant);

        $response = $this->runMiddleware($this->makeRequestForRoute('tenant.feature.demo'));

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('/pricing', (string) $response->headers->get('Location'));
    }

    // 7 ─────────────────────────────────────────────────────────────────
    public function test_excluded_routes_bypass_middleware(): void
    {
        // Even with an EXPIRED subscription, exempt routes must pass through.
        [$tenant, $user, $sub] = $this->seedTenant(SubscriptionStatus::Expired);
        TenantContext::set($tenant);

        $exemptRoutes = [
            'tenant.billing.index',
            'checkout.show',
            'tenant.password.setup',
            'login',
            'webhooks.moyasar',
            'health.overall',
            'marketing.pricing',
            'tenant.suspended',
        ];

        foreach ($exemptRoutes as $name) {
            $response = $this->runMiddleware($this->makeRequestForRoute($name));

            $this->assertSame(200, $response->getStatusCode(),
                "Exempt route '{$name}' should pass through but got {$response->getStatusCode()}");
        }
    }

    // 8 ─────────────────────────────────────────────────────────────────
    public function test_central_routes_not_affected(): void
    {
        // Real central requests have no tenant + fallback turned off.
        // We disable the fallback locally so TenantContext::current()
        // doesn't auto-resolve the default tenant for this assertion.
        TenantContext::forget();
        config(['tenancy.fallback_enabled' => false]);

        $response = $this->runMiddleware($this->makeRequestForRoute('tenant.feature.demo'));

        $this->assertSame(200, $response->getStatusCode());
    }

    // 9 ─────────────────────────────────────────────────────────────────
    public function test_no_500_errors_in_any_state(): void
    {
        $states = [
            SubscriptionStatus::Active,
            SubscriptionStatus::Trialing,
            SubscriptionStatus::PastDue,
            SubscriptionStatus::Expired,
            SubscriptionStatus::Canceled,
            SubscriptionStatus::Paused,
        ];

        foreach ($states as $state) {
            [$tenant, $user, $sub] = $this->seedTenant($state);
            TenantContext::set($tenant);

            $response = $this->runMiddleware($this->makeRequestForRoute('tenant.feature.demo'));

            $this->assertNotSame(500, $response->getStatusCode(),
                "State '{$state->value}' should never produce 500.");
            $this->assertContains($response->getStatusCode(), [200, 302, 403],
                "State '{$state->value}' returned unexpected status {$response->getStatusCode()}.");
        }
    }

    // ────────────────────────────────────────────────────────── helpers

    /**
     * Run the middleware against a Request whose route resolves to the
     * given name. The "next" closure simulates a downstream "OK" handler.
     */
    private function runMiddleware(Request $request)
    {
        $middleware = app(CheckSubscription::class);

        return $middleware->handle($request, fn () => response('OK', 200));
    }

    /**
     * Build a Request that knows its route name — required for
     * `$request->routeIs()` checks inside the middleware.
     */
    private function makeRequestForRoute(string $routeName): Request
    {
        $request = Request::create('/dummy', 'GET');

        $route = new RoutingRoute(['GET'], '/dummy', static fn () => 'ok');
        $route->name($routeName);
        $request->setRouteResolver(fn () => $route);

        return $request;
    }

    /**
     * @return array{0: Tenant, 1: User, 2: Subscription}
     */
    private function seedTenant(SubscriptionStatus $status): array
    {
        $tenant = Tenant::create([
            'name'   => 'CS Co '.uniqid(),
            'slug'   => 'cs-'.uniqid(),
            'status' => Tenant::STATUS_ACTIVE,
        ]);

        $plan = Plan::create([
            'slug'          => 'cs-plan-'.uniqid(),
            'name'          => 'CS Plan',
            'description'   => '...',
            'price_monthly' => 100,
            'price_yearly'  => 1000,
            'currency'      => 'SAR',
            'trial_days'    => 14,
            'is_active'     => true,
            'is_featured'   => false,
            'is_free'       => false,
            'sort_order'    => 1,
        ]);

        $user = TenantContext::runAs($tenant, fn () => User::create([
            'name'                => 'CS Owner',
            'email'               => 'cs-owner-'.uniqid().'@cs.test',
            'password'            => Hash::make('placeholder-'.uniqid()),
            'nationality'         => 'SA',
            'tour_completed'      => 0,
            'tour_task_completed' => 0,
        ]));

        $now = now();
        $sub = Subscription::withoutTenancy()->create([
            'tenant_id'                 => $tenant->id,
            'plan_id'                   => $plan->id,
            'status'                    => $status->value,
            'billing_cycle'             => BillingCycle::Monthly->value,
            'currency'                  => $plan->currency,
            'gateway'                   => PaymentGateway::Moyasar->value,
            'trial_ends_at'             => $status === SubscriptionStatus::Trialing ? $now->copy()->addDays(7) : null,
            'current_period_started_at' => $now,
            'current_period_ends_at'    => $now->copy()->addMonth(),
        ]);

        return [$tenant, $user, $sub];
    }
}
