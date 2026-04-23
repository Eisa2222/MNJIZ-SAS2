<?php

declare(strict_types=1);

namespace Tests\Feature\Billing;

use App\Enums\Billing\PaymentGateway;
use App\Enums\Billing\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\WebhookEvent;
use App\Tenancy\TenantContext;
use Database\Seeders\DefaultPlansSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Ensures Moyasar webhook handling is:
 *   1. Tamper-resistant (wrong secret_token → 401)
 *   2. Idempotent (same event_id delivered twice → processed once)
 *   3. Correctly correlates to our Payment row
 */
final class WebhookIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DefaultPlansSeeder::class);

        // Bind a known webhook secret so our test payloads are "verified".
        config(['services.moyasar.webhook_secret' => 'test_webhook_secret_for_moyasar']);
    }

    public function test_unsigned_webhook_is_rejected_with_401(): void
    {
        $response = $this->postJson('/webhooks/moyasar', [
            'id'   => 'evt_1',
            'type' => 'payment_paid',
            'data' => ['id' => 'pay_1', 'status' => 'paid'],
            // no secret_token → verifier should reject
        ]);

        $response->assertStatus(401);
        $this->assertSame(0, WebhookEvent::count());
    }

    public function test_verified_webhook_marks_payment_captured_and_is_idempotent(): void
    {
        [$payment] = $this->scaffoldPayment();

        $payload = [
            'id'            => 'evt_9999',
            'type'          => 'payment_paid',
            'data'          => ['id' => $payment->gateway_payment_id, 'status' => 'paid'],
            'secret_token'  => 'test_webhook_secret_for_moyasar',
        ];

        // 1st delivery → processed
        $r1 = $this->postJson('/webhooks/moyasar', $payload);
        $r1->assertOk()->assertJson(['status' => 'ok']);

        $this->assertSame(PaymentStatus::Captured, $payment->fresh()->status);
        $this->assertSame(1, WebhookEvent::where('event_id', 'evt_9999')->count());
        $this->assertNotNull(WebhookEvent::where('event_id', 'evt_9999')->first()->processed_at);

        // 2nd delivery (retry) → short-circuited
        $r2 = $this->postJson('/webhooks/moyasar', $payload);
        $r2->assertOk()->assertJson(['status' => 'already_processed']);

        // Still ONE webhook_event row, payment still captured.
        $this->assertSame(1, WebhookEvent::where('event_id', 'evt_9999')->count());
        $this->assertSame(PaymentStatus::Captured, $payment->fresh()->status);
    }

    public function test_failed_payment_webhook_marks_payment_failed(): void
    {
        [$payment] = $this->scaffoldPayment();

        $payload = [
            'id'           => 'evt_failed_1',
            'type'         => 'payment_failed',
            'data'         => ['id' => $payment->gateway_payment_id, 'status' => 'failed'],
            'secret_token' => 'test_webhook_secret_for_moyasar',
        ];

        $this->postJson('/webhooks/moyasar', $payload)->assertOk();

        $this->assertSame(PaymentStatus::Failed, $payment->fresh()->status);
        $this->assertNotNull($payment->fresh()->failed_at);
    }

    /** @return array{0: Payment, 1: Invoice, 2: Tenant} */
    private function scaffoldPayment(): array
    {
        $tenant = Tenant::create(['name' => 'W', 'slug' => 'w-'.uniqid(), 'status' => 'active']);
        $plan   = Plan::where('slug', 'starter')->firstOrFail();

        return TenantContext::runAs($tenant, function () use ($tenant, $plan) {
            $sub = \App\Models\Subscription::create([
                'tenant_id'     => $tenant->id,
                'plan_id'       => $plan->id,
                'status'        => 'active',
                'billing_cycle' => 'monthly',
                'currency'      => 'SAR',
                'gateway'       => 'moyasar',
            ]);

            $invoice = new Invoice();
            $invoice->tenant_id       = $tenant->id;
            $invoice->subscription_id = $sub->id;
            $invoice->number          = 'INV-TEST-'.uniqid();
            $invoice->status          = 'open';
            $invoice->subtotal        = 100;
            $invoice->total           = 100;
            $invoice->currency        = 'SAR';
            $invoice->save();

            $payment = new Payment();
            $payment->tenant_id          = $tenant->id;
            $payment->invoice_id         = $invoice->id;
            $payment->status             = PaymentStatus::Pending;
            $payment->amount             = 100;
            $payment->currency           = 'SAR';
            $payment->gateway            = PaymentGateway::Moyasar;
            $payment->gateway_payment_id = 'pay_test_'.uniqid();
            $payment->save();

            return [$payment, $invoice, $tenant];
        });
    }
}
