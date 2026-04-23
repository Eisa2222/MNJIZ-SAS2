<?php

declare(strict_types=1);

namespace App\Console\Commands\Billing;

use App\Actions\Billing\Subscription\MarkPastDueAction;
use App\Actions\Billing\Subscription\RenewSubscriptionAction;
use App\Enums\Billing\SubscriptionStatus;
use App\Models\Subscription;
use App\Tenancy\TenantContext;
use Illuminate\Console\Command;
use Throwable;

/**
 * Renews subscriptions whose current_period_ends_at has passed.
 * For each:
 *   1. Roll period forward via RenewSubscriptionAction (creates invoice).
 *   2. The created invoice is in DRAFT/OPEN — a separate charge step would
 *      charge it via ChargeInvoiceAction. For Phase 5 we leave that to the
 *      per-tenant self-serve checkout; auto-charge stored cards is a Phase
 *      8 concern (needs tokenized sources, not in-scope now).
 *   3. If renewal itself fails, mark past due.
 *
 * Schedule: daily at 00:10 (after the day boundary).
 */
final class ChargeDueSubscriptionsCommand extends Command
{
    protected $signature = 'billing:charge-due-subscriptions
                            {--dry-run : Print actions without executing}';
    protected $description = 'Roll active subscriptions forward into their next billing period and create invoices.';

    public function handle(RenewSubscriptionAction $renew, MarkPastDueAction $markPastDue): int
    {
        $query = Subscription::withoutTenancy()
            ->where('status', SubscriptionStatus::Active->value)
            ->where('current_period_ends_at', '<=', now());

        $total    = $query->count();
        $renewed  = 0;
        $pastDue  = 0;

        $this->info("Found {$total} subscriptions due for renewal.");

        $query->chunkById(100, function ($subs) use (&$renewed, &$pastDue, $renew, $markPastDue) {
            foreach ($subs as $sub) {
                /** @var Subscription $sub */
                if ($this->option('dry-run')) {
                    $this->line("[dry-run] would renew subscription #{$sub->id} tenant={$sub->tenant_id}");
                    continue;
                }

                TenantContext::runAs($sub->tenantRelation, function () use ($sub, $renew, $markPastDue, &$renewed, &$pastDue) {
                    try {
                        $renew->execute($sub);
                        $renewed++;
                    } catch (Throwable $e) {
                        $this->error("Renewal failed for #{$sub->id}: {$e->getMessage()}");
                        try {
                            $markPastDue->execute($sub);
                            $pastDue++;
                        } catch (Throwable $ignored) {
                            // already past_due or terminal
                        }
                    }
                });
            }
        });

        $this->info("Renewed: {$renewed}  Past-due: {$pastDue}");

        return self::SUCCESS;
    }
}
