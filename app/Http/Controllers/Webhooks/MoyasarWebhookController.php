<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhooks;

use App\Actions\Billing\ProcessWebhookAction;
use App\Contracts\Billing\PaymentServiceInterface;
use App\Enums\Billing\PaymentGateway;
use App\Http\Controllers\Controller;
use App\Models\WebhookEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * POST /webhooks/moyasar
 *
 * Contract:
 *   - Always return 200 unless signature verification fails (return 401) or
 *     the payload is unparseable (400). Moyasar retries on non-2xx, so 5xx
 *     would trigger a retry storm.
 *   - Idempotent: same event_id is safe to deliver multiple times. The
 *     UNIQUE(gateway, event_id) constraint on webhook_events prevents
 *     double-processing even under race conditions.
 *   - Signature-verified payloads are logged to `webhook_events` BEFORE
 *     processing, so even if the handler crashes we have a forensic record.
 */
final class MoyasarWebhookController extends Controller
{
    public function __construct(
        private PaymentServiceInterface $moyasar,
        private ProcessWebhookAction $processor,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $raw = $request->getContent();

        try {
            $payload = $this->moyasar->parseWebhook($raw, $request->headers->all());
        } catch (\Throwable $e) {
            Log::warning('moyasar.webhook.rejected', [
                'error' => $e->getMessage(),
                'ip'    => $request->ip(),
            ]);

            return response()->json(['error' => 'invalid signature'], 401);
        }

        // Idempotency: firstOrCreate wins the race safely on the UNIQUE index.
        $event = WebhookEvent::query()->firstOrCreate(
            [
                'gateway'  => PaymentGateway::Moyasar->value,
                'event_id' => $payload->eventId,
            ],
            [
                'event_type'  => $payload->eventType,
                'payload'     => $payload->raw,
                'signature'   => (string) ($payload->raw['secret_token'] ?? null),
                'verified'    => $payload->verified,
                'received_at' => now(),
            ]
        );

        if ($event->isProcessed()) {
            // Already handled on an earlier delivery. 200 tells Moyasar to stop.
            return response()->json(['status' => 'already_processed']);
        }

        try {
            $this->processor->execute($event, $payload);
        } catch (\Throwable $e) {
            $event->markFailed($e->getMessage());
            Log::error('moyasar.webhook.processing_failed', [
                'event_id' => $payload->eventId,
                'error'    => $e->getMessage(),
            ]);

            // Still return 200 so Moyasar doesn't retry-storm. Admin will
            // see unprocessed events in the Super Admin webhook log page.
            return response()->json(['status' => 'recorded_but_not_processed'], 200);
        }

        return response()->json(['status' => 'ok']);
    }
}
