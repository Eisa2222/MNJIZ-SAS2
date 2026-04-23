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
use App\Exceptions\Billing\PaymentFailedException;
use RuntimeException;

/**
 * Moyasar is a Saudi Arabian payment gateway supporting mada / visa /
 * mastercard / apple_pay / stc_pay. Amounts are sent in HALALAS — 1 SAR = 100
 * halalas.
 *
 * Design note: the caller (ChargeInvoiceAction) converts the ChargeRequest
 * into a MOyasar-shaped payload only inside this class — keeping the action
 * and the rest of the system gateway-agnostic.
 */
final class MoyasarPaymentService implements PaymentServiceInterface
{
    public function __construct(
        private MoyasarClient $client,
        private MoyasarWebhookVerifier $verifier,
    ) {}

    public function gatewayName(): string
    {
        return PaymentGateway::Moyasar->value;
    }

    public function charge(ChargeRequest $request): ChargeResult
    {
        $payload = [
            'amount'      => $this->toHalalas($request->amount),
            'currency'    => strtoupper($request->currency),
            'description' => $request->description,
            'source'      => [
                'type'  => 'token',
                'token' => $request->sourceToken,
            ],
            'callback_url' => $request->callbackUrl,
            'metadata'     => array_merge($request->metadata, [
                'tenant_id'  => (string) $request->tenant->id,
                'invoice_id' => (string) $request->invoice->id,
            ]),
        ];

        $response = $this->client->createPayment($payload);

        return $this->mapChargeResponse($response, $request->amount, $request->currency);
    }

    public function refund(RefundRequest $request): RefundResult
    {
        $response = $this->client->refund($request->payment->gateway_payment_id ?? '', [
            'amount' => $this->toHalalas($request->amount),
        ]);

        $status = match ($response['status'] ?? 'pending') {
            'refunded', 'succeeded' => RefundStatus::Succeeded,
            'failed'                => RefundStatus::Failed,
            default                 => RefundStatus::Pending,
        };

        return new RefundResult(
            status:          $status,
            gatewayRefundId: (string) ($response['id'] ?? ''),
            amount:          $request->amount,
            failureReason:   $response['failure_reason'] ?? null,
            raw:             $response,
        );
    }

    public function parseWebhook(string $rawBody, array $headers): WebhookPayload
    {
        $decoded = json_decode($rawBody, true) ?: [];

        if (! $this->verifier->verify($decoded)) {
            throw new RuntimeException('Moyasar webhook signature mismatch.');
        }

        // Moyasar shape: { id (event id), type, data: { id (payment id), status, ... } }
        $eventId   = (string) ($decoded['id']   ?? '');
        $eventType = (string) ($decoded['type'] ?? 'unknown');
        $data      = $decoded['data'] ?? [];

        if ($eventId === '') {
            throw new RuntimeException('Moyasar webhook missing event id.');
        }

        return new WebhookPayload(
            gateway:          PaymentGateway::Moyasar,
            eventId:          $eventId,
            eventType:        $eventType,
            gatewayPaymentId: isset($data['id']) ? (string) $data['id'] : null,
            raw:              $decoded,
            verified:         true,
        );
    }

    // -------------------------------------------------------------------------

    private function mapChargeResponse(array $response, float $amount, string $currency): ChargeResult
    {
        $status = $this->mapPaymentStatus((string) ($response['status'] ?? 'pending'));
        $source = $response['source'] ?? [];

        $redirectUrl = null;

        // 3DS flow: Moyasar returns source.transaction_url when redirection is needed.
        if ($status === PaymentStatus::Pending && ! empty($source['transaction_url'])) {
            $redirectUrl = (string) $source['transaction_url'];
        }

        if ($status === PaymentStatus::Failed) {
            throw new PaymentFailedException(
                gatewayPaymentId: (string) ($response['id'] ?? ''),
                reason:           (string) ($response['source']['message'] ?? $response['message'] ?? 'declined'),
                raw:              $response,
            );
        }

        return new ChargeResult(
            status:           $status,
            gatewayPaymentId: (string) ($response['id'] ?? ''),
            amount:           $amount,
            currency:         $currency,
            redirectUrl:      $redirectUrl,
            cardLast4:        $this->pickLast4($source),
            cardBrand:        $source['company']    ?? null,
            sourceType:       $response['source']['type'] ?? null,
            failureReason:    null,
            raw:              $response,
        );
    }

    private function mapPaymentStatus(string $raw): PaymentStatus
    {
        return match (strtolower($raw)) {
            'paid', 'captured', 'authorized' => PaymentStatus::Captured,
            'failed', 'voided'               => PaymentStatus::Failed,
            'refunded'                       => PaymentStatus::Refunded,
            default                          => PaymentStatus::Pending,
        };
    }

    private function pickLast4(array $source): ?string
    {
        if (! empty($source['number'])) {
            return substr((string) $source['number'], -4);
        }

        return $source['last_four'] ?? null;
    }

    private function toHalalas(float $amount): int
    {
        return (int) round($amount * 100);
    }
}
