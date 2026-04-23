<?php

declare(strict_types=1);

namespace App\Services\Billing\Gateway;

/**
 * Moyasar signs webhook bodies with the "secret_token" you configured in the
 * Moyasar dashboard. They include it as plain text in the payload under
 * "secret_token". We compare in constant time.
 *
 * (Moyasar's webhook model differs from Stripe's HMAC-header approach.
 * Keep this class here so the pattern is ready when we add Stripe/HyperPay.)
 */
final class MoyasarWebhookVerifier
{
    public function __construct(private string $expectedSecret) {}

    public function verify(array $payload): bool
    {
        $provided = (string) ($payload['secret_token'] ?? '');

        if ($provided === '' || $this->expectedSecret === '') {
            return false;
        }

        return hash_equals($this->expectedSecret, $provided);
    }
}
