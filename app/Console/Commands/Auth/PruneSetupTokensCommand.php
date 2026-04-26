<?php

declare(strict_types=1);

namespace App\Console\Commands\Auth;

use App\Services\Auth\TenantPasswordSetupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Phase H+ — garbage-collect stale rows from `tenant_password_setup_tokens`.
 *
 * The token signed-URL TTL is 48 hours (TenantPasswordSetupService::VALID_HOURS).
 * `verify()` already rejects rows older than that AND the URL signature
 * itself enforces the same expiry — so leaving stale rows behind is
 * functionally harmless, but the table grows unbounded over time. This
 * command sweeps it nightly.
 *
 * Cut-off uses `VALID_HOURS * 2` so we never delete a row that COULD still
 * be inside the signed-URL window due to clock skew. After that any row
 * is provably expired from both the application AND the URL signature.
 *
 * Schedule: registered in app/Console/Kernel.php at 03:00 daily (well
 * outside the 00:00–08:00 trial-lifecycle window so workloads don't pile
 * up).
 *
 * Idempotent — re-running deletes whatever's currently stale.
 */
final class PruneSetupTokensCommand extends Command
{
    protected $signature = 'saas:prune-setup-tokens
                            {--dry-run : Print row count without deleting}';

    protected $description = 'Delete password-setup tokens older than 2× VALID_HOURS (defensive grace).';

    public function handle(): int
    {
        $cutoff = now()->subHours(TenantPasswordSetupService::VALID_HOURS * 2);

        $query = DB::table('tenant_password_setup_tokens')
            ->where('created_at', '<', $cutoff);

        $count = (clone $query)->count();
        $this->info("Found {$count} setup tokens older than {$cutoff->toIso8601String()}.");

        if ($count === 0) {
            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->line('[dry-run] no rows deleted.');
            return self::SUCCESS;
        }

        $deleted = $query->delete();
        $this->info("Deleted {$deleted} stale setup tokens.");

        return self::SUCCESS;
    }
}
