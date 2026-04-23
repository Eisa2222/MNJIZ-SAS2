<?php

declare(strict_types=1);

namespace Tests\Feature\Billing;

use App\DTOs\Billing\ChargeRequest;
use App\Enums\Billing\PaymentStatus;
use App\Exceptions\Billing\PaymentFailedException;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Services\Billing\Gateway\MoyasarClient;
use App\Services\Billing\Gateway\MoyasarPaymentService;
use App\Services\Billing\Gateway\MoyasarWebhookVerifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Exercises MoyasarPaymentService against Laravel's HTTP client fake — no
 * real network calls. This is the gateway-logic contract test.
 */
final class MoyasarGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_charge_succeeds_when_moyasar_returns_paid(): void
    {
        Http::fake([
            'api.moyasar.com/v1/payments' => Http::response([
                'id'     => 'pay_test_ok_1',
                'status' => 'paid',
                'amount' => 10000, // 100 SAR in halalas
                'source' => ['type' => 'creditcard', 'company' => 'visa', 'number' => '************4242'],
            ], 201),
        ]);

        $service = new MoyasarPaymentService(
            new MoyasarClient('sk_test_fake', 'https://api.moyasar.com/v1'),
            new MoyasarWebhookVerifier('whsec_fake'),
        );

        $result = $service->charge(new ChargeRequest(
            tenant:      new Tenant(['id' => 1, 'slug' => 'demo']),
            invoice:     new Invoice(['id' => 1, 'number' => 'INV-001']),
            amount:      100.00,
            currency:    'SAR',
            sourceToken: 'token_test_123',
            description: 'Test',
        ));

        $this->assertSame(PaymentStatus::Captured, $result->status);
        $this->assertSame('pay_test_ok_1', $result->gatewayPaymentId);
        $this->assertSame('4242', $result->cardLast4);
        $this->assertSame('visa', $result->cardBrand);
    }

    public function test_charge_throws_payment_failed_exception_on_decline(): void
    {
        Http::fake([
            'api.moyasar.com/v1/payments' => Http::response([
                'id'     => 'pay_test_fail_1',
                'status' => 'failed',
                'source' => ['type' => 'creditcard', 'message' => 'insufficient_funds'],
            ], 200),
        ]);

        $service = new MoyasarPaymentService(
            new MoyasarClient('sk_test_fake'),
            new MoyasarWebhookVerifier('whsec_fake'),
        );

        $this->expectException(PaymentFailedException::class);
        $this->expectExceptionMessageMatches('/insufficient_funds/');

        $service->charge(new ChargeRequest(
            tenant:      new Tenant(['id' => 1, 'slug' => 'demo']),
            invoice:     new Invoice(['id' => 1, 'number' => 'INV-001']),
            amount:      100.00,
            currency:    'SAR',
            sourceToken: 'bad_token',
            description: 'Test',
        ));
    }

    public function test_parse_webhook_rejects_missing_signature(): void
    {
        $service = new MoyasarPaymentService(
            new MoyasarClient('sk_test_fake'),
            new MoyasarWebhookVerifier('whsec_fake'),
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/signature/i');

        $service->parseWebhook(json_encode(['id' => 'evt_1', 'type' => 'payment_paid']), []);
    }

    public function test_parse_webhook_accepts_matching_secret(): void
    {
        $service = new MoyasarPaymentService(
            new MoyasarClient('sk_test_fake'),
            new MoyasarWebhookVerifier('whsec_fake'),
        );

        $payload = [
            'id'           => 'evt_abc_1',
            'type'         => 'payment_paid',
            'data'         => ['id' => 'pay_xyz'],
            'secret_token' => 'whsec_fake',
        ];

        $result = $service->parseWebhook(json_encode($payload), []);

        $this->assertSame('evt_abc_1', $result->eventId);
        $this->assertSame('payment_paid', $result->eventType);
        $this->assertSame('pay_xyz', $result->gatewayPaymentId);
        $this->assertTrue($result->verified);
    }
}
