<?php

declare(strict_types=1);

namespace App\Actions\Billing;

use App\Contracts\Billing\PaymentServiceInterface;
use App\DTOs\Billing\RefundRequest;
use App\Enums\Billing\InvoiceStatus;
use App\Enums\Billing\PaymentStatus;
use App\Enums\Billing\RefundStatus;
use App\Events\Billing\PaymentRefunded;
use App\Models\Payment;
use App\Models\Refund;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class RefundPaymentAction
{
    /** @param array<string, PaymentServiceInterface> $gatewayMap */
    public function __construct(private array $gatewayMap = []) {}

    public function execute(Payment $payment, ?float $amount = null, ?string $reason = null): Refund
    {
        if (! $payment->isRefundable()) {
            throw new RuntimeException("Payment #{$payment->id} is not refundable.");
        }

        $amount ??= $payment->remainingRefundable();
        if ($amount <= 0) {
            throw new RuntimeException('Refund amount must be positive.');
        }
        if ($amount > $payment->remainingRefundable()) {
            throw new RuntimeException('Refund amount exceeds remaining refundable amount.');
        }

        $driver = $this->gatewayMap[$payment->gateway->value]
            ?? throw new RuntimeException("No gateway driver for '{$payment->gateway->value}'.");

        return DB::transaction(function () use ($payment, $amount, $reason, $driver) {
            $result = $driver->refund(new RefundRequest(
                payment: $payment,
                amount:  $amount,
                reason:  $reason,
            ));

            $refund = new Refund();
            $refund->payment_id        = $payment->id;
            $refund->tenant_id         = $payment->tenant_id;
            $refund->status            = $result->status;
            $refund->amount            = $result->amount;
            $refund->currency          = $payment->currency;
            $refund->reason            = $reason;
            $refund->gateway_refund_id = $result->gatewayRefundId;
            $refund->refunded_at       = $result->status === RefundStatus::Succeeded ? now() : null;
            $refund->failure_reason    = $result->failureReason;
            $refund->meta              = $result->raw;
            $refund->save();

            if ($result->status === RefundStatus::Succeeded) {
                $payment->amount_refunded = (float) $payment->amount_refunded + $amount;
                $payment->status = ((float) $payment->amount_refunded >= (float) $payment->amount)
                    ? PaymentStatus::Refunded
                    : PaymentStatus::PartiallyRefunded;
                $payment->save();

                // Cascade to the invoice.
                if ($invoice = $payment->invoice) {
                    $invoice->amount_refunded = (float) $invoice->amount_refunded + $amount;
                    $invoice->status = ((float) $invoice->amount_refunded >= (float) $invoice->amount_paid)
                        ? InvoiceStatus::RefundedFull
                        : InvoiceStatus::RefundedPartial;
                    $invoice->save();
                }

                event(new PaymentRefunded($payment, $refund));
            }

            activity('billing')
                ->performedOn($payment)
                ->withProperties([
                    'tenant_id' => $payment->tenant_id,
                    'refund_id' => $refund->id,
                    'amount'    => $amount,
                ])
                ->event('payment.refunded')
                ->log("Payment #{$payment->id} refunded (amount={$amount} {$payment->currency}, status={$result->status->value}).");

            return $refund;
        });
    }
}
