<?php

declare(strict_types=1);

namespace App\Console\Commands\Billing;

use App\Actions\Billing\Subscription\MarkPastDueAction;
use App\Enums\Billing\InvoiceStatus;
use App\Enums\Billing\SubscriptionStatus;
use App\Models\Subscription;
use App\Tenancy\TenantContext;
use Illuminate\Console\Command;

/**
 * Catches Active subscriptions that have an open unpaid invoice past its
 * due date. Idempotent — MarkPastDueAction no-ops if already past_due.
 *
 * Schedule: daily at 00:15.
 */
final class MarkPastDueSubscriptionsCommand extends Command
{
    protected $signature = 'billing:mark-past-due';
    protected $description = 'Move active subscriptions with overdue unpaid invoices to past_due.';

    public function handle(MarkPastDueAction $markPastDue): int
    {
        $query = Subscription::withoutTenancy()
            ->where('status', SubscriptionStatus::Active->value)
            ->whereHas('invoices', function ($q) {
                $q->where('status', InvoiceStatus::Open->value)
                  ->whereNotNull('due_at')
                  ->where('due_at', '<', now());
            });

        $count = 0;

        $query->chunkById(100, function ($subs) use ($markPastDue, &$count) {
            foreach ($subs as $sub) {
                TenantContext::runAs($sub->tenantRelation, function () use ($sub, $markPastDue, &$count) {
                    $markPastDue->execute($sub);
                    $count++;
                });
            }
        });

        $this->info("Moved {$count} subscription(s) to past_due.");

        return self::SUCCESS;
    }
}
