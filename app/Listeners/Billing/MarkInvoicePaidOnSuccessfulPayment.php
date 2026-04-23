<?php

declare(strict_types=1);

namespace App\Listeners\Billing;

use App\Enums\Billing\InvoiceStatus;
use App\Events\Billing\InvoicePaid;

/**
 * Closes the invoice when a Payment row moves to Captured. Keeps the rule
 * "an invoice is paid when its running total of captured payments ≥ its total"
 * in a single place — ChargeInvoiceAction fires the event, this listener
 * persists the state change.
 */
final class MarkInvoicePaidOnSuccessfulPayment
{
    public function handle(InvoicePaid $event): void
    {
        $invoice = $event->invoice->fresh(['payments']);

        if (! $invoice) {
            return;
        }

        $capturedTotal = $invoice->payments
            ->where('status', \App\Enums\Billing\PaymentStatus::Captured)
            ->sum('amount');

        $invoice->amount_paid = $capturedTotal;

        if ($capturedTotal >= (float) $invoice->total) {
            $invoice->status  = InvoiceStatus::Paid;
            $invoice->paid_at = now();
        }

        $invoice->save();
    }
}
