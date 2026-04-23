<?php

declare(strict_types=1);

namespace App\Actions\Billing\Subscription;

use App\Actions\Billing\Invoice\CreateInvoiceAction;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

/**
 * Called by the scheduler when current_period_ends_at <= now and the
 * subscription is still Active. Rolls the period forward and creates the
 * next invoice.
 *
 * The caller (ChargeDueInvoicesCommand) then charges the invoice via
 * ChargeInvoiceAction. If charge fails, MarkPastDueAction runs.
 */
final class RenewSubscriptionAction
{
    public function __construct(private CreateInvoiceAction $createInvoice) {}

    public function execute(Subscription $subscription): Subscription
    {
        return DB::transaction(function () use ($subscription) {
            $oldEnd = $subscription->current_period_ends_at ?? now();

            $subscription->current_period_started_at = $oldEnd;
            $subscription->current_period_ends_at    = $subscription->billing_cycle->addToNow($oldEnd->toImmutable());
            $subscription->save();

            $this->createInvoice->execute($subscription, $subscription->coupon);

            activity('billing')
                ->performedOn($subscription)
                ->withProperties([
                    'tenant_id'          => $subscription->tenant_id,
                    'new_period_ends_at' => $subscription->current_period_ends_at?->toIso8601String(),
                ])
                ->event('subscription.renewed')
                ->log("Subscription #{$subscription->id} renewed.");

            return $subscription;
        });
    }
}
