<?php

declare(strict_types=1);

namespace App\Services\Billing\Gateway;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Thin HTTP client over api.moyasar.com. Authenticates via HTTP Basic auth
 * with the secret key. Keeps timeouts + retries configurable.
 *
 * Moyasar endpoints used:
 *   POST /v1/payments            — create charge
 *   POST /v1/payments/{id}/refund — refund
 *   GET  /v1/payments/{id}        — read payment (for webhook verification)
 */
final class MoyasarClient
{
    public function __construct(
        private string $secretKey,
        private string $baseUrl = 'https://api.moyasar.com/v1',
        private int $timeoutSeconds = 30,
    ) {}

    public function createPayment(array $payload): array
    {
        return $this->post('/payments', $payload);
    }

    public function getPayment(string $paymentId): array
    {
        return $this->get("/payments/{$paymentId}");
    }

    public function refund(string $paymentId, array $payload): array
    {
        return $this->post("/payments/{$paymentId}/refund", $payload);
    }

    private function http(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout($this->timeoutSeconds)
            ->withBasicAuth($this->secretKey, '')
            ->acceptJson()
            ->asJson()
            ->retry(2, 500, throw: false);
    }

    private function post(string $path, array $payload): array
    {
        $response = $this->http()->post($path, $payload);

        return $this->unwrap($response, "POST {$path}");
    }

    private function get(string $path): array
    {
        $response = $this->http()->get($path);

        return $this->unwrap($response, "GET {$path}");
    }

    private function unwrap(Response $response, string $context): array
    {
        $body = $response->json();

        if ($response->failed()) {
            $message = is_array($body) ? json_encode($body, JSON_UNESCAPED_UNICODE) : (string) $response->body();

            throw new RuntimeException("Moyasar {$context} failed [{$response->status()}]: {$message}");
        }

        return is_array($body) ? $body : [];
    }
}
