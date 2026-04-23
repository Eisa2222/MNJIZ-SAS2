<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\Billing\Invoice\ChargeInvoiceAction;
use App\Actions\Billing\RefundPaymentAction;
use App\Contracts\Billing\PaymentServiceInterface;
use App\Enums\Billing\PaymentGateway;
use App\Events\Billing\InvoicePaid;
use App\Events\Billing\PaymentFailed;
use App\Events\Billing\SubscriptionActivated;
use App\Events\Billing\SubscriptionCanceled;
use App\Events\Billing\SubscriptionExpired;
use App\Listeners\Billing\AssignPlanOnSubscriptionActivated;
use App\Listeners\Billing\FlushFeatureCacheOnSubscriptionChange;
use App\Listeners\Billing\MarkInvoicePaidOnSuccessfulPayment;
use App\Listeners\Billing\NotifyTenantOnPaymentFailed;
use App\Listeners\Billing\RevertToFreePlanOnSubscriptionExpired;
use App\Services\Billing\Gateway\ManualPaymentService;
use App\Services\Billing\Gateway\MoyasarClient;
use App\Services\Billing\Gateway\MoyasarPaymentService;
use App\Services\Billing\Gateway\MoyasarWebhookVerifier;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Wires up the Phase 5 billing layer:
 *   - Registers Moyasar + Manual gateway drivers in the container,
 *     keyed by PaymentGateway enum value.
 *   - Resolves PaymentServiceInterface → MoyasarPaymentService by default
 *     (the webhook controller needs a single concrete binding).
 *   - Registers event listeners for subscription + payment lifecycle.
 *   - Loads routes/webhooks.php for /webhooks/moyasar endpoint.
 */
final class BillingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/billing.php', 'billing');

        // Per-gateway concrete driver bindings.
        $this->app->singleton(MoyasarClient::class, function () {
            return new MoyasarClient(
                secretKey: (string) config('services.moyasar.secret_key', env('MOYASAR_SECRET_KEY', '')),
                baseUrl:   (string) config('services.moyasar.base_url', 'https://api.moyasar.com/v1'),
                timeoutSeconds: (int) config('services.moyasar.timeout', 30),
            );
        });

        $this->app->singleton(MoyasarWebhookVerifier::class, function () {
            return new MoyasarWebhookVerifier(
                expectedSecret: (string) config('services.moyasar.webhook_secret', env('MOYASAR_WEBHOOK_SECRET', '')),
            );
        });

        $this->app->singleton(MoyasarPaymentService::class);
        $this->app->singleton(ManualPaymentService::class);

        // Default binding for the interface — the webhook controller takes
        // the interface and resolves the Moyasar concrete. Actions that need
        // per-gateway routing receive the array map below.
        $this->app->bind(PaymentServiceInterface::class, MoyasarPaymentService::class);

        // Actions that must dispatch to the RIGHT driver receive a keyed map.
        $this->app->when([ChargeInvoiceAction::class, RefundPaymentAction::class])
            ->needs('$gatewayMap')
            ->give(function () {
                return [
                    PaymentGateway::Moyasar->value => $this->app->make(MoyasarPaymentService::class),
                    PaymentGateway::Manual->value  => $this->app->make(ManualPaymentService::class),
                ];
            });
    }

    public function boot(): void
    {
        // Listeners — declared here (not in EventServiceProvider) so the
        // billing module stays self-contained.
        Event::listen(SubscriptionActivated::class, AssignPlanOnSubscriptionActivated::class);
        Event::listen(SubscriptionExpired::class,   RevertToFreePlanOnSubscriptionExpired::class);
        Event::listen(InvoicePaid::class,           MarkInvoicePaidOnSuccessfulPayment::class);
        Event::listen(PaymentFailed::class,         NotifyTenantOnPaymentFailed::class);
        Event::listen([
            SubscriptionActivated::class,
            SubscriptionCanceled::class,
            SubscriptionExpired::class,
        ], FlushFeatureCacheOnSubscriptionChange::class);

        // Webhook routes live under /webhooks/* — central, unauthenticated.
        $this->loadRoutesFrom(base_path('routes/webhooks.php'));
    }
}
