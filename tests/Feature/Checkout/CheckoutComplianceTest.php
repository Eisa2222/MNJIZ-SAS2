<?php

declare(strict_types=1);

namespace Tests\Feature\Checkout;

use App\Models\Coupon;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\Billing\Gateway\MoyasarClient;
use App\Services\MoyasarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Phase E — Checkout flow regression suite.
 *
 *   1. checkout page loads
 *   2. apply coupon AJAX returns valid JSON
 *   3. invalid coupon AJAX returns valid=false
 *   4. callback rejects unpaid payment
 *   5. callback creates payment/subscription/tenant on paid payment
 *   6. coupon_code metadata applied (sub.coupon_id wired up)
 *   7. success page loads
 *   8. failure page loads
 *   9. no secret appears in checkout view
 *  10. landing pricing links point to checkout
 */
final class CheckoutComplianceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Stub Moyasar HTTP client so getPayment never hits the real API.
        // We swap the underlying MoyasarClient with one whose secret-key
        // is "test-secret" — the wrapper service still works with it.
        $this->app->singleton(MoyasarClient::class, function () {
            return new MoyasarClient('test-secret');
        });
    }

    // 1 ─────────────────────────────────────────────────────────────────
    public function test_checkout_page_loads(): void
    {
        $plan = $this->makePlan();

        $this->get("/checkout/{$plan->slug}")
            ->assertOk()
            ->assertSee($plan->name)
            ->assertSee('Moyasar', false);
    }

    // 2 ─────────────────────────────────────────────────────────────────
    public function test_apply_coupon_ajax_returns_valid_json(): void
    {
        $plan = $this->makePlan();
        $this->makeCoupon(['code' => 'OK10', 'type' => 'percentage', 'value' => 10]);

        $r = $this->postJson('/checkout/apply-coupon', [
            'code'          => 'OK10',
            'plan_id'       => $plan->id,
            'billing_cycle' => 'monthly',
            'amount'        => 200,
        ]);

        $r->assertOk();
        $r->assertJson([
            'valid'        => true,
            'discount'     => 20,
            'final_amount' => 180,
            'code'         => 'OK10',
        ]);
    }

    // 3 ─────────────────────────────────────────────────────────────────
    public function test_invalid_coupon_ajax_returns_valid_false(): void
    {
        $plan = $this->makePlan();

        $r = $this->postJson('/checkout/apply-coupon', [
            'code'          => 'NOPE',
            'plan_id'       => $plan->id,
            'billing_cycle' => 'monthly',
            'amount'        => 200,
        ]);

        $r->assertOk();
        $r->assertJson(['valid' => false, 'discount' => 0]);
    }

    // 4 ─────────────────────────────────────────────────────────────────
    public function test_callback_rejects_unpaid_payment(): void
    {
        Http::fake([
            'api.moyasar.com/*' => Http::response([
                'id'       => 'pay_unpaid_999',
                'status'   => 'failed',
                'amount'   => 9900,
                'currency' => 'SAR',
                'metadata' => [],
            ], 200),
        ]);

        $r = $this->get('/checkout/callback?id=pay_unpaid_999');

        $r->assertRedirect();
        $this->assertStringContainsString('checkout/failure', $r->headers->get('Location') ?? '');
        $this->assertSame(0, Payment::query()->withoutGlobalScopes()->count());
    }

    // 5 ─────────────────────────────────────────────────────────────────
    public function test_callback_creates_payment_subscription_tenant_on_paid(): void
    {
        $plan = $this->makePlan();

        Http::fake([
            'api.moyasar.com/*' => Http::response([
                'id'       => 'pay_PAID_555',
                'status'   => 'paid',
                'amount'   => 10000, // 100.00 SAR in halalas
                'currency' => 'SAR',
                'source'   => ['type' => 'creditcard', 'company' => 'mada', 'number' => 'XXXX-XXXX-XXXX-1234'],
                'metadata' => [
                    'plan_id'       => (string) $plan->id,
                    'billing_cycle' => 'monthly',
                    'company_name'  => 'Acme Law',
                    'owner_name'    => 'Alice',
                    'owner_email'   => 'alice@acme.test',
                    'owner_phone'   => '+966500000000',
                    'coupon_code'   => '',
                ],
            ], 200),
        ]);

        $r = $this->get('/checkout/callback?id=pay_PAID_555');

        $r->assertRedirect();
        $this->assertStringContainsString('checkout/success', $r->headers->get('Location') ?? '');

        $tenant = Tenant::query()->where('name', 'Acme Law')->first();
        $this->assertNotNull($tenant);

        $subscription = Subscription::query()->withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)->first();
        $this->assertNotNull($subscription);
        $this->assertSame($plan->id, $subscription->plan_id);

        $payment = Payment::query()->withoutGlobalScopes()
            ->where('gateway_payment_id', 'pay_PAID_555')->first();
        $this->assertNotNull($payment);
        $this->assertSame('1234', $payment->card_last4);
    }

    // 6 ─────────────────────────────────────────────────────────────────
    public function test_coupon_code_metadata_applied_on_callback(): void
    {
        $plan   = $this->makePlan();
        $coupon = $this->makeCoupon(['code' => 'CKMETA', 'type' => 'fixed', 'value' => 20]);

        Http::fake([
            'api.moyasar.com/*' => Http::response([
                'id'       => 'pay_META_111',
                'status'   => 'paid',
                'amount'   => 8000, // 80 SAR after 20 SAR discount on a 100 SAR plan
                'currency' => 'SAR',
                'source'   => ['type' => 'creditcard'],
                'metadata' => [
                    'plan_id'       => (string) $plan->id,
                    'billing_cycle' => 'monthly',
                    'company_name'  => 'Beta LLC',
                    'owner_name'    => 'Bob',
                    'owner_email'   => 'bob@beta.test',
                    'owner_phone'   => '+966500000001',
                    'coupon_code'   => 'CKMETA',
                ],
            ], 200),
        ]);

        $this->get('/checkout/callback?id=pay_META_111')->assertRedirect();

        $tenant = Tenant::query()->where('name', 'Beta LLC')->first();
        $this->assertNotNull($tenant);

        $subscription = Subscription::query()->withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)->first();
        $this->assertSame($coupon->id, $subscription->coupon_id);
        $this->assertSame(1, $coupon->fresh()->redemptions_count);

        $this->assertDatabaseHas('coupon_uses', [
            'coupon_id'       => $coupon->id,
            'tenant_id'       => $tenant->id,
            'subscription_id' => $subscription->id,
        ]);
    }

    // 7 ─────────────────────────────────────────────────────────────────
    public function test_success_page_loads(): void
    {
        $this->get('/checkout/success')
            ->assertOk()
            ->assertSee(__('checkout.success.title'));
    }

    // 8 ─────────────────────────────────────────────────────────────────
    public function test_failure_page_loads(): void
    {
        $this->get('/checkout/failure?reason=unpaid')
            ->assertOk()
            ->assertSee(__('checkout.failure.title'));
    }

    // 9 ─────────────────────────────────────────────────────────────────
    public function test_no_secret_appears_in_checkout_view(): void
    {
        \App\Models\SystemSetting::set('moyasar_secret_key', 'sk_LEAK_NEVER_xyz', [
            'group' => 'moyasar', 'is_encrypted' => true,
        ]);
        \App\Models\SystemSetting::set('moyasar_publishable_key', 'pk_safe_to_leak_abc', [
            'group' => 'moyasar',
        ]);

        $plan = $this->makePlan();

        $r = $this->get("/checkout/{$plan->slug}");

        $r->assertOk();
        $r->assertDontSee('sk_LEAK_NEVER_xyz');
        // The publishable key IS rendered (front-end safe).
        $r->assertSee('pk_safe_to_leak_abc');
    }

    // 10 ────────────────────────────────────────────────────────────────
    public function test_landing_pricing_links_point_to_checkout(): void
    {
        $plan = $this->makePlan(['slug' => 'go-plan']);

        $r = $this->get('/');

        $r->assertOk();
        $r->assertSee('/checkout/go-plan', false);
    }

    // ────────────────────────────────────────────────────────── helpers

    private function makePlan(array $overrides = []): Plan
    {
        $base = [
            'slug'          => 'ck-plan-'.uniqid(),
            'name'          => 'Checkout Plan',
            'description'   => '...',
            'price_monthly' => 100,
            'price_yearly'  => 1000,
            'currency'      => 'SAR',
            'trial_days'    => 0,
            'is_active'     => true,
            'is_featured'   => false,
            'is_free'       => false,
            'sort_order'    => 1,
        ];

        return Plan::create(array_merge($base, $overrides));
    }

    private function makeCoupon(array $overrides = []): Coupon
    {
        $base = [
            'code'              => 'CK'.strtoupper(uniqid()),
            'name'              => 'CK Coupon',
            'type'              => 'percentage',
            'value'             => 10,
            'currency'          => 'SAR',
            'duration'          => 'once',
            'applies_to'        => 'any',
            'is_active'         => true,
            'redemptions_count' => 0,
        ];

        return Coupon::create(array_merge($base, $overrides));
    }
}
