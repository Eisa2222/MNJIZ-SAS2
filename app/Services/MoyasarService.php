<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Billing\Gateway\MoyasarClient;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Phase E — spec-shaped façade over the existing Phase 5
 * `App\Services\Billing\Gateway\MoyasarClient`.
 *
 * The reference SaaS prompt mandates a `MoyasarService` with these
 * three methods:
 *
 *   createPayment(array $payload): array
 *   getPayment(string $paymentId): array
 *   refundPayment(string $paymentId, array $payload): array
 *
 * This adapter exposes them and delegates to the existing
 * MoyasarClient — there's no duplication of HTTP, retry, or
 * Basic-Auth logic. Phase 5's `MoyasarPaymentService` (the
 * `PaymentServiceInterface` implementation) keeps its richer DTO API
 * for ChargeInvoiceAction; this Service is for the public checkout
 * surface where we want raw arrays.
 *
 * Secret-key sourcing: the underlying MoyasarClient is constructed in
 * BillingServiceProvider from `config('services.moyasar.secret_key')`.
 * `ApplySystemSettings` middleware (Phase C) overrides that config at
 * runtime from `system_settings.moyasar_secret_key`, so the operator's
 * Super-Admin-saved key is what actually goes on the wire.
 *
 * Logging discipline: NEVER log the secret key; NEVER log full payment
 * payloads (they may contain PCI-relevant tokens). On exception we log
 * a generic message + the gateway payment id (if known) only.
 */
final class MoyasarService
{
    public function __construct(private MoyasarClient $client) {}

    /**
     * Create a Moyasar payment.
     *
     * Amount in `$payload['amount']` MUST already be in halalas
     * (1 SAR = 100). Callers that work in SAR should call
     * `static::toHalalas()` first.
     *
     * @param  array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createPayment(array $payload): array
    {
        try {
            return $this->client->createPayment($payload);
        } catch (\Throwable $e) {
            Log::warning('moyasar.create_payment_failed', [
                'message' => $e->getMessage(),
                // intentionally NOT logging $payload — may contain a token.
            ]);
            throw $e;
        }
    }

    /**
     * Read a payment by id (used by the 3DS callback to verify status
     * after the user is redirected back from Moyasar's hosted page).
     *
     * @return array<string, mixed>
     */
    public function getPayment(string $paymentId): array
    {
        return $this->client->getPayment($paymentId);
    }

    /**
     * Refund a payment, fully or partially.
     *
     * @param  array<string, mixed> $payload  e.g. ['amount' => 1500]
     * @return array<string, mixed>
     */
    public function refundPayment(string $paymentId, array $payload): array
    {
        return $this->client->refund($paymentId, $payload);
    }

    /**
     * SAR → halalas helper. 1 SAR = 100 halalas. Use for the `amount`
     * field of `createPayment` payloads.
     */
    public static function toHalalas(float $amountInSar): int
    {
        return (int) round($amountInSar * 100);
    }

    /**
     * Convenience accessor for the publishable (front-end safe) key.
     * Used by Blade views to bootstrap Moyasar.js. The secret key is
     * NEVER exposed to a Blade view.
     */
    public static function publishableKey(): string
    {
        return (string) (Config::get('services.moyasar.publishable_key') ?? '');
    }

    /**
     * Convenience accessor for the operator-enabled methods list. Falls
     * back to the standard 3-method set if no override is in
     * system_settings.
     *
     * @return array<int, string>
     */
    public static function enabledMethods(): array
    {
        $methods = Config::get('services.moyasar.enabled_methods');

        if (is_array($methods) && count($methods) > 0) {
            return array_values(array_filter($methods, 'is_string'));
        }

        return ['creditcard', 'applepay', 'stcpay'];
    }
}
