<?php

declare(strict_types=1);

namespace App\DTOs\Billing;

use App\Models\Invoice;
use App\Models\Tenant;

/**
 * Input to PaymentServiceInterface::charge().
 *
 * We never pass raw card data — the tenant UI uses the gateway's hosted
 * tokenization (Moyasar widget) and sends a one-shot source token in
 * $sourceToken. This keeps us out of PCI scope.
 */
final class ChargeRequest
{
    public function __construct(
        public readonly Tenant $tenant,
        public readonly Invoice $invoice,
        public readonly float $amount,
        public readonly string $currency,
        public readonly string $sourceToken,    // from Moyasar.js / tokenization
        public readonly string $description,
        public readonly ?string $callbackUrl = null,  // 3DS return URL
        public readonly array $metadata = [],
    ) {}
}
