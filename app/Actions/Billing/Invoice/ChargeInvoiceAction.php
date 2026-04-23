<?php

declare(strict_types=1);

namespace App\Actions\Billing\Invoice;

use App\Contracts\Billing\PaymentServiceInterface;
use App\DTOs\Billing\ChargeRequest;
use App\DTOs\Billing\ChargeResult;
use App\Enums\Billing\InvoiceStatus;
use App\Enums\Billing\PaymentGateway;
use App\Enums\Billing\PaymentStatus;
use App\Events\Billing\InvoicePaid;
use App\Events\Billing\PaymentFailed;
use App\Exceptions\Billing\PaymentFailedException;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * The single entry point for "take money from the customer for this invoice".
 * Caller supplies the gateway name + a pre-tokenized source (from Moyasar.js).
 *
 *   1. Open the invoice if still draft.
 *   2. Ask the gateway to capture.
 *   3. Record a Payment row with the gateway's response.
 *   4. Log the round-trip in payment_attempts.
 *   5. If success → event(InvoicePaid) → listener marks invoice paid.
 *      If failure → event(PaymentFailed) → queued notify listener.
 */
final class ChargeInvoiceAction
{
    /** @param array<string, PaymentServiceInterface> $gatewayMap service name → driver */
    public function __construct(private array $gatewayMap = []) {}

    public function execute(
        Invoice $invoice,
        PaymentGateway $gateway,
        string $sourceToken,
        ?string $callbackUrl = null,
    ): Payment {
        $driver = $this->driverFor($gateway);

        return DB::transaction(function () use ($invoice, $gateway, $sourceToken, $callbackUrl, $driver) {
            if ($invoice->status === InvoiceStatus::Draft) {
                $invoice->status = InvoiceStatus::Open;
                $invoice->save();
            }

            if ($invoice->isFullyPaid()) {
                throw new RuntimeException("Invoice {$invoice->number} is already fully paid.");
            }

            $amount = $invoice->outstandingAmount();

            $payment = new Payment();
            $payment->tenant_id = $invoice->tenant_id;
            $payment->invoice_id = $invoice->id;
            $payment->status    = PaymentStatus::Pending;
            $payment->amount    = $amount;
            $payment->currency  = $invoice->currency;
            $payment->gateway   = $gateway;
            $payment->save();

            try {
                // BelongsToTenant trait exposes ->tenant() on Invoice. Subscription
                // uses the aliased `tenantRelation` only because it defines a plan()
                // belongsTo too — Invoice doesn't have that conflict.
                $tenant = $invoice->tenant
                    ?? \App\Models\Tenant::query()->findOrFail($invoice->tenant_id);

                $result = $driver->charge(new ChargeRequest(
                    tenant:      $tenant,
                    invoice:     $invoice,
                    amount:      $amount,
                    currency:    $invoice->currency,
                    sourceToken: $sourceToken,
                    description: "Invoice {$invoice->number}",
                    callbackUrl: $callbackUrl,
                ));

                $this->applyChargeResult($payment, $invoice, $result);
            } catch (PaymentFailedException $e) {
                $this->applyFailure($payment, $invoice, $e->reason, $e->raw);
                throw $e;
            } catch (\Throwable $e) {
                $this->applyFailure($payment, $invoice, $e->getMessage(), []);
                throw $e;
            }

            return $payment->fresh();
        });
    }

    private function driverFor(PaymentGateway $gateway): PaymentServiceInterface
    {
        if (! isset($this->gatewayMap[$gateway->value])) {
            throw new RuntimeException("No gateway driver registered for '{$gateway->value}'.");
        }

        return $this->gatewayMap[$gateway->value];
    }

    private function applyChargeResult(Payment $payment, Invoice $invoice, ChargeResult $result): void
    {
        $payment->status             = $result->status;
        $payment->gateway_payment_id = $result->gatewayPaymentId;
        $payment->card_last4         = $result->cardLast4;
        $payment->card_brand         = $result->cardBrand;
        $payment->source_type        = $result->sourceType;

        if ($result->isSuccessful()) {
            $payment->paid_at = now();
        }

        $payment->save();

        PaymentAttempt::create([
            'payment_id'       => $payment->id,
            'status'           => $result->status->value,
            'gateway_event_id' => $result->gatewayPaymentId,
            'gateway_response' => $result->raw,
            'attempted_at'     => now(),
        ]);

        if ($result->isSuccessful()) {
            event(new InvoicePaid($invoice->fresh(), $payment));
        }
    }

    private function applyFailure(Payment $payment, Invoice $invoice, string $reason, array $raw): void
    {
        $payment->status         = PaymentStatus::Failed;
        $payment->failed_at      = now();
        $payment->failure_reason = $reason;
        $payment->save();

        PaymentAttempt::create([
            'payment_id'       => $payment->id,
            'status'           => PaymentStatus::Failed->value,
            'error_message'    => $reason,
            'gateway_response' => $raw,
            'attempted_at'     => now(),
        ]);

        event(new PaymentFailed($payment, $reason));
    }
}
