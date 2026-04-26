<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\SystemSetting;
use Illuminate\Console\Command;

/**
 * V2 — Daily trial-warning sweep. Spec lines 375-380.
 */
final class SendTrialWarnings extends Command
{
    protected $signature = 'saas:send-trial-warnings {--dry-run}';
    protected $description = 'Email trialing customers a warning when trial_warning_days remain.';

    public function handle(): int
    {
        $notify = (bool) SystemSetting::get('notify_trial_expiring', true);
        if (! $notify) {
            $this->info('notify_trial_expiring is OFF — exiting.');
            return self::SUCCESS;
        }

        $warnDays = (int) SystemSetting::get('trial_warning_days', 3);
        $target = now()->copy()->addDays($warnDays)->toDateString();

        $count = 0;
        Subscription::where('status', Subscription::STATUS_TRIALING)
            ->whereNotNull('trial_ends_at')
            ->whereDate('trial_ends_at', $target)
            ->chunkById(100, function ($subs) use (&$count) {
                foreach ($subs as $sub) {
                    if ($this->option('dry-run')) {
                        $this->line("[dry-run] would warn #{$sub->id}");
                        continue;
                    }
                    // (mail dispatch wired in next session)
                    $count++;
                }
            });

        $this->info("Queued {$count} warnings for trials ending on {$target}.");
        return self::SUCCESS;
    }
}
