<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\SystemSetting;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * V2 — Daily trial-expiry sweep. Spec lines 367-373.
 */
final class CheckTrialExpiry extends Command
{
    protected $signature = 'saas:check-trial-expiry {--dry-run}';
    protected $description = 'Expire trialing subscriptions whose trial_ends_at has passed.';

    public function handle(): int
    {
        $suspend = (bool) SystemSetting::get('trial_suspend_after_expiry', true);
        $notify  = (bool) SystemSetting::get('notify_trial_expiring', true);

        $expired = 0;
        Subscription::where('status', Subscription::STATUS_TRIALING)
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<=', now())
            ->chunkById(100, function ($subs) use ($suspend, $notify, &$expired) {
                foreach ($subs as $sub) {
                    if ($this->option('dry-run')) {
                        $this->line("[dry-run] would expire #{$sub->id}");
                        continue;
                    }
                    DB::transaction(function () use ($sub, $suspend) {
                        $sub->update([
                            'status'  => Subscription::STATUS_EXPIRED,
                            'ends_at' => $sub->ends_at ?? now(),
                        ]);
                        if ($suspend) {
                            Tenant::where('id', $sub->tenant_id)->update(['status' => Tenant::STATUS_SUSPENDED]);
                        }
                    });
                    $expired++;
                    // (mail dispatch will be wired in next session)
                }
            });

        $this->info("Expired {$expired} subscriptions.");
        return self::SUCCESS;
    }
}
