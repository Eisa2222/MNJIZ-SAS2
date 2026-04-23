<?php

declare(strict_types=1);

namespace App\Actions\Billing\Invoice;

use App\Enums\Billing\InvoiceStatus;
use App\Events\Billing\InvoiceCreated;
use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\TaxRate;
use Illuminate\Support\Facades\DB;

/**
 * Builds an invoice for a subscription's current period.
 *
 * Math:
 *   subtotal     = plan price for cycle
 *   discount     = coupon.computeDiscount(subtotal), if any
 *   tax          = tax_rate.compute(subtotal - discount), if any
 *   total        = subtotal - discount + tax
 *
 * Returns a DRAFT invoice — caller (ChargeInvoiceAction or scheduler) moves
 * it to OPEN then triggers payment.
 */
final class CreateInvoiceAction
{
    public function execute(
        Subscription $subscription,
        ?Coupon $coupon = null,
        ?TaxRate $taxRate = null,
    ): Invoice {
        return DB::transaction(function () use ($subscription, $coupon, $taxRate) {
            /** @var Plan $plan */
            $plan  = $subscription->plan;
            $cycle = $subscription->billing_cycle;

            $subtotal = $cycle->priceOn($plan);
            $discount = $coupon && $coupon->isRedeemable($subtotal, $plan)
                            ? $coupon->computeDiscount($subtotal)
                            : 0.0;

            $taxRate ??= TaxRate::defaultActive();
            $taxable = max(0, $subtotal - $discount);
            $tax     = $taxRate ? $taxRate->compute($taxable) : 0.0;
            $total   = round($taxable + $tax, 2);

            $invoice = new Invoice();
            $invoice->tenant_id        = $subscription->tenant_id;
            $invoice->subscription_id  = $subscription->id;
            $invoice->coupon_id        = $coupon?->id;
            $invoice->tax_rate_id      = $taxRate?->id;
            $invoice->number           = $this->nextInvoiceNumber();
            $invoice->status           = InvoiceStatus::Draft;
            $invoice->subtotal         = $subtotal;
            $invoice->tax_amount       = $tax;
            $invoice->discount_amount  = $discount;
            $invoice->total            = $total;
            $invoice->currency         = $subscription->currency;
            $invoice->period_start     = $subscription->current_period_started_at;
            $invoice->period_end       = $subscription->current_period_ends_at;
            $invoice->due_at           = now()->addDays(7);
            $invoice->issued_at        = now();
            $invoice->save();

            $invoice->items()->create([
                'description' => "Subscription — {$plan->name} ({$cycle->label()})",
                'quantity'    => 1,
                'unit_price'  => $subtotal,
                'amount'      => $subtotal,
            ]);

            event(new InvoiceCreated($invoice));

            activity('billing')
                ->performedOn($invoice)
                ->withProperties(['tenant_id' => $subscription->tenant_id])
                ->event('invoice.created')
                ->log("Invoice {$invoice->number} created (total {$total} {$subscription->currency}).");

            return $invoice;
        });
    }

    /**
     * Sequential, monotonic, human-readable — "INV-{YYYY}-{6-digit}".
     */
    private function nextInvoiceNumber(): string
    {
        $year = now()->year;
        $last = Invoice::withoutTenancy()
            ->where('number', 'like', "INV-{$year}-%")
            ->orderByDesc('id')
            ->value('number');

        $seq = $last ? ((int) substr($last, -6)) + 1 : 1;

        return sprintf('INV-%d-%06d', $year, $seq);
    }
}
