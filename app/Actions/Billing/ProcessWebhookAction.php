<?php

declare(strict_types=1);

namespace App\Actions\Billing;

use App\Actions\Billing\Subscription\ActivateSubscriptionAction;
use App\DTOs\Billing\WebhookPayload;
use App\Enums\Billing\PaymentGateway;
use App\Enums\Billing\PaymentStatus;
use App\Events\Billing\InvoicePaid;
use App\Events\Billing\PaymentFailed;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\WebhookEvent;
use Illuminate\Support\Facades\DB;

/**
 * Applies a verified webhook payload to our state:
 *
 *   - correlates payment via gateway_payment_id
 *   - moves payment status (paid → Captured, failed → Failed, …)
 *   - fires InvoicePaid / PaymentFailed so listeners update invoice + plan
 *
 * Idempotency is enforced by the caller (WebhookController) via the UNIQUE
 * (gateway, event_id) constraint on webhook_events. This action is safe to
 * call twice on the same event — it just no-ops.
 */
final class ProcessWebhookAction
{
    public function __construct(private ActivateSubscriptionAction $activate) {}

    public function execute(WebhookEvent $event, WebhookPayload $payload): void
    {
        if ($event->isProcessed()) {
            return;
        }

        DB::transaction(function () use ($event, $payload) {
            $payment = $this->correlatePayment($payload);

            if ($payment) {
                $event->payment_id = $payment->id;
                $event->tenant_id  = $payment->tenant_id;

                $this->dispatchByType($payload, $payment);
            }

            $event->markProcessed();
        });
    }

    private function correlatePayment(WebhookPayload $payload): ?Payment
    {
        if (! $payload->gatewayPaymentId) {
            return null;
        }

        return Payment::withoutTenancy()
            ->where('gateway', $payload->gateway->value)
            ->where('gateway_payment_id', $payload->gatewayPaymentId)
            ->first();
    }

    private function dispatchByType(WebhookPayload $payload, Payment $payment): void
    {
        $type = strtolower($payload->eventType);

        // Normalize common gateway event names into our internal semantics.
        $isPaid     = str_contains($type, 'paid')     || str_contains($type, 'capture') || str_contains($type, 'success');
        $isFailed   = str_contains($type, 'fail')     || str_contains($type, 'declin');
        $isRefunded = str_contains($type, 'refund');

        if ($isPaid && $payment->status !== PaymentStatus::Captured) {
            $payment->status  = PaymentStatus::Captured;
            $payment->paid_at = now();
            $payment->save();

            if ($invoice = $payment->invoice) {
                event(new InvoicePaid($invoice->fresh(), $payment));
                $this->maybeActivateSubscription($invoice->subscription_id);
            }
            return;
        }

        if ($isFailed && $payment->status !== PaymentStatus::Failed) {
            $payment->status         = PaymentStatus::Failed;
            $payment->failed_at      = now();
            $payment->failure_reason = 'gateway-reported failure';
            $payment->save();

            event(new PaymentFailed($payment, 'gateway-reported failure'));
            return;
        }

        if ($isRefunded) {
            // Refund flows are handled by RefundPaymentAction (caller-initiated).
            // Gateway-initiated refunds (chargebacks) are recorded here as a
            // Refund row with the gateway's id — Phase 8 polish.
            return;
        }
    }

    private function maybeActivateSubscription(?int $subscriptionId): void
    {
        if (! $subscriptionId) {
            return;
        }

        /** @var Subscription|null $sub */
        $sub = Subscription::withoutTenancy()->find($subscriptionId);

        if (! $sub) {
            return;
        }

        try {
            $this->activate->execute($sub);
        } catch (\Throwable $e) {
            // Already active or terminal — safe to swallow.
        }
    }
}
