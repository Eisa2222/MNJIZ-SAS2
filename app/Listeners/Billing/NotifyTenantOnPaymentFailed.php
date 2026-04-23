<?php

declare(strict_types=1);

namespace App\Listeners\Billing;

use App\Events\Billing\PaymentFailed;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Queued — notifying users is side-effectful and must not block the webhook
 * response thread. Phase 8 will wire a proper PaymentFailedNotification mail
 * template. For now we log + leave the hook point.
 */
final class NotifyTenantOnPaymentFailed implements ShouldQueue
{
    public function handle(PaymentFailed $event): void
    {
        $payment = $event->payment;

        // Find tenant-scoped admins to notify. Scope must be bypassed here —
        // listener runs in queue worker context, tenant may not be set.
        $admins = User::withoutTenancy()
            ->where('tenant_id', $payment->tenant_id)
            ->where('status', 'active')
            ->limit(5)
            ->get();

        Log::warning('billing.payment_failed', [
            'payment_id' => $payment->id,
            'tenant_id'  => $payment->tenant_id,
            'amount'     => (float) $payment->amount,
            'reason'     => $event->reason,
            'admins'     => $admins->pluck('email')->all(),
        ]);

        // TODO (Phase 8): dispatch PaymentFailedNotification to each admin.
    }
}
