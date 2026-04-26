<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SystemSetting;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

/**
 * V2 — Moyasar payment gateway client.
 *
 * Spec lines 195-198:
 *   createPayment(), getPayment(), refundPayment()
 *   + auto SAR → halalas conversion (×100)
 *
 * Reads keys at call time from system_settings (operator-managed),
 * falling back to env. Secret key is NEVER logged.
 */
final class MoyasarService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = (string) (env('MOYASAR_BASE_URL', 'https://api.moyasar.com/v1'));
    }

    public static function publishableKey(): ?string
    {
        return SystemSetting::get('moyasar_publishable_key', env('MOYASAR_PUBLISHABLE_KEY'));
    }

    public function secretKey(): ?string
    {
        return SystemSetting::get('moyasar_secret_key', env('MOYASAR_SECRET_KEY'));
    }

    public static function enabledMethods(): array
    {
        $configured = SystemSetting::get('moyasar_enabled_methods');
        if (is_array($configured) && count($configured) > 0) {
            return $configured;
        }
        return ['creditcard', 'applepay', 'stcpay'];
    }

    /**
     * @param  array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function createPayment(array $payload): array
    {
        $payload['amount']   = self::toHalalas((float) ($payload['amount'] ?? 0));
        $payload['currency'] = strtoupper((string) ($payload['currency'] ?? 'SAR'));

        return $this->request('POST', '/payments', $payload);
    }

    /** @return array<string,mixed> */
    public function getPayment(string $id): array
    {
        return $this->request('GET', "/payments/{$id}");
    }

    /** @return array<string,mixed> */
    public function refundPayment(string $id, ?float $amount = null): array
    {
        $body = $amount !== null ? ['amount' => self::toHalalas($amount)] : [];
        return $this->request('POST', "/payments/{$id}/refund", $body);
    }

    public static function toHalalas(float $amountInSar): int
    {
        return (int) round($amountInSar * 100);
    }

    private function request(string $method, string $path, array $body = []): array
    {
        $secret = $this->secretKey();
        if ($secret === null || $secret === '') {
            throw new RuntimeException('Moyasar secret key is not configured. Set it in /super-admin/settings.');
        }

        $client = new Client([
            'base_uri' => $this->baseUrl,
            'auth'     => [$secret, ''],
            'timeout'  => 30,
        ]);

        try {
            $response = $client->request($method, $path, [
                'json' => $body,
                'http_errors' => false,
            ]);
            $data = json_decode((string) $response->getBody(), true) ?: [];

            if ($response->getStatusCode() >= 400) {
                throw new RuntimeException(
                    'Moyasar '.$method.' '.$path.' failed: '
                    .($data['message'] ?? 'HTTP '.$response->getStatusCode())
                );
            }
            return $data;
        } catch (GuzzleException $e) {
            throw new RuntimeException('Moyasar transport error: '.$e->getMessage(), 0, $e);
        }
    }
}
