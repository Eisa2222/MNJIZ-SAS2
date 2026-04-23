<?php

declare(strict_types=1);

namespace App\DTOs\Billing;

use App\Enums\Billing\PaymentGateway;

/**
 * Normalized webhook envelope. Each gateway's driver converts its native
 * payload into this shape before handing to ProcessWebhookAction.
 */
final class WebhookPayload
{
    public function __construct(
        public readonly PaymentGateway $gateway,
        public readonly string $eventId,
        public readonly string $eventType,       // e.g. "payment.paid", "payment.failed", "payment.refunded"
        public readonly ?string $gatewayPaymentId,
        public readonly array $raw,
        public readonly bool $verified,
    ) {}
}
