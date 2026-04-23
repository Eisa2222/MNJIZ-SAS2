<?php

declare(strict_types=1);

namespace App\Services\Billing\Gateway;

use App\Contracts\Billing\PaymentServiceInterface;
use App\DTOs\Billing\ChargeRequest;
use App\DTOs\Billing\ChargeResult;
use App\DTOs\Billing\RefundRequest;
use App\DTOs\Billing\RefundResult;
use App\DTOs\Billing\WebhookPayload;
use App\Enums\Billing\PaymentGateway;
use App\Enums\Billing\PaymentStatus;
use App\Enums\Billing\RefundStatus;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * "Offline" rail: bank transfer, cash, or Super Admin manually marking an
 * invoice paid. There's no gateway API call — we synthesize an ID and return
 * Captured immediately. Refunds are no-ops at the gateway level (admin must
 * return the money out-of-band; we record the accounting).
 *
 * NEVER exposed to self-serve checkout — only used from the Super Admin panel.
 */
final class ManualPaymentService implements PaymentServiceInterface
{
    public function gatewayName(): string
    {
        return PaymentGateway::Manual->value;
    }

    public function charge(ChargeRequest $request): ChargeResult
    {
        return new ChargeResult(
            status:           PaymentStatus::Captured,
            gatewayPaymentId: 'manual_'.Str::uuid()->toString(),
            amount:           $request->amount,
            currency:         $request->currency,
            raw:              ['gateway' => 'manual', 'recorded_at' => now()->toIso8601String()],
        );
    }

    public function refund(RefundRequest $request): RefundResult
    {
        return new RefundResult(
            status:          RefundStatus::Succeeded,
            gatewayRefundId: 'manual_refund_'.Str::uuid()->toString(),
            amount:          $request->amount,
            raw:             ['gateway' => 'manual'],
        );
    }

    public function parseWebhook(string $rawBody, array $headers): WebhookPayload
    {
        throw new RuntimeException('Manual gateway does not emit webhooks.');
    }
}
