<?php

declare(strict_types=1);

namespace App\Contracts\Billing;

use App\DTOs\Billing\ChargeRequest;
use App\DTOs\Billing\ChargeResult;
use App\DTOs\Billing\RefundRequest;
use App\DTOs\Billing\RefundResult;
use App\DTOs\Billing\WebhookPayload;

/**
 * Gateway-agnostic contract. Every provider (Moyasar, Stripe, HyperPay,
 * Manual) implements this. Actions + Controllers depend on the interface,
 * never on the concrete.
 *
 * DI binding lives in BillingServiceProvider — chosen per request based on
 * the target subscription.gateway (or a default from config).
 */
interface PaymentServiceInterface
{
    /**
     * Capture payment against an invoice.
     * For 3DS: ChargeResult::requires3DS() true + redirectUrl populated.
     */
    public function charge(ChargeRequest $request): ChargeResult;

    /**
     * Full or partial refund.
     */
    public function refund(RefundRequest $request): RefundResult;

    /**
     * Validate the raw webhook request (signature, timestamp, etc.) and
     * return a normalized payload. Throws on tamper / replay.
     */
    public function parseWebhook(string $rawBody, array $headers): WebhookPayload;

    public function gatewayName(): string;
}
