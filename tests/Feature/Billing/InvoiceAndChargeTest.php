<?php

declare(strict_types=1);

namespace Tests\Feature\Billing;

use App\Actions\Billing\Invoice\ChargeInvoiceAction;
use App\Actions\Billing\Invoice\CreateInvoiceAction;
use App\Actions\Billing\Subscription\StartTrialAction;
use App\Enums\Billing\InvoiceStatus;
use App\Enums\Billing\PaymentGateway;
use App\Enums\Billing\PaymentStatus;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Tenant;
use App\Services\Billing\Gateway\ManualPaymentService;
use App\Tenancy\TenantContext;
use Database\Seeders\DefaultPlansSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

final class InvoiceAndChargeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DefaultPlansSeeder::class);
    }

    public function test_create_invoice_computes_subtotal_tax_and_total(): void
    {
        [$sub, $tenant] = $this->tenantWithSub('starter');

        $invoice = TenantContext::runAs($tenant, fn () => app(CreateInvoiceAction::class)->execute($sub));

        $this->assertSame(InvoiceStatus::Draft, $invoice->status);
        $this->assertEqualsWithDelta(299.00, (float) $invoice->subtotal, 0.01);
        $this->assertEqualsWithDelta(0.00,  (float) $invoice->discount_amount, 0.01);
        $this->assertCount(1, $invoice->items);
        $this->assertStringStartsWith('INV-', $invoice->number);
    }

    public function test_charge_invoice_with_manual_gateway_marks_invoice_paid(): void
    {
        [$sub, $tenant] = $this->tenantWithSub('starter');

        // Use Manual gateway only for this test — no external HTTP needed.
        $chargeAction = new ChargeInvoiceAction([
            PaymentGateway::Manual->value => new ManualPaymentService(),
        ]);

        $invoice = TenantContext::runAs($tenant, fn () => app(CreateInvoiceAction::class)->execute($sub));
        $payment = TenantContext::runAs($tenant, fn () => $chargeAction->execute(
            invoice:     $invoice,
            gateway:     PaymentGateway::Manual,
            sourceToken: 'manual-payment',
        ));

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertSame(PaymentStatus::Captured, $payment->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertSame(InvoiceStatus::Paid, $invoice->fresh()->status);
        $this->assertEqualsWithDelta(
            (float) $invoice->total,
            (float) $invoice->fresh()->amount_paid,
            0.01,
        );
    }

    public function test_charge_double_pay_on_paid_invoice_is_rejected(): void
    {
        [$sub, $tenant] = $this->tenantWithSub('starter');

        $chargeAction = new ChargeInvoiceAction([
            PaymentGateway::Manual->value => new ManualPaymentService(),
        ]);

        $invoice = TenantContext::runAs($tenant, fn () => app(CreateInvoiceAction::class)->execute($sub));
        TenantContext::runAs($tenant, fn () => $chargeAction->execute(
            $invoice, PaymentGateway::Manual, 'x',
        ));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/already fully paid/i');

        TenantContext::runAs($tenant, fn () => $chargeAction->execute(
            $invoice->fresh(), PaymentGateway::Manual, 'x',
        ));
    }

    /** @return array{0: \App\Models\Subscription, 1: Tenant} */
    private function tenantWithSub(string $planSlug): array
    {
        $tenant = Tenant::create(['name' => 'T', 'slug' => 't-'.uniqid(), 'status' => 'active']);
        $plan   = Plan::where('slug', $planSlug)->firstOrFail();
        $sub    = TenantContext::runAs($tenant, fn () => app(StartTrialAction::class)->execute($tenant, $plan));

        return [$sub, $tenant->fresh()];
    }
}
