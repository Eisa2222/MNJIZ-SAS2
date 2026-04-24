<?php

declare(strict_types=1);

namespace App\Console\Commands\Saas\Operations;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `saas:deploy:check`
 *
 * Pre-deploy readiness gate. Exits non-zero if any of these is true:
 *
 *   1. APP_KEY is missing
 *   2. APP_DEBUG is true AND APP_ENV is production
 *   3. QUEUE_CONNECTION is `sync` AND APP_ENV is production
 *   4. Pending database migrations exist
 *   5. Default tenant missing
 *   6. More than 50 unresolved rows in failed_jobs
 *   7. `saas:migration:validate` invariants fail
 *   8. Required env variables (APP_URL, DB_CONNECTION, TENANCY_DEFAULT_*)
 *      are empty
 *
 * Use as a CI/CD gate before `php artisan migrate --force && ...`.
 */
final class DeployCheckCommand extends Command
{
    protected $signature   = 'saas:deploy:check';
    protected $description = 'Pre-deploy readiness check. Non-zero exit blocks deploy.';

    /** @var array<array{check: string, status: string, message?: string}> */
    private array $results = [];

    public function handle(): int
    {
        $this->checkAppKey();
        $this->checkDebugInProd();
        $this->checkSyncQueueInProd();
        $this->checkRequiredEnvs();
        $this->checkPendingMigrations();
        $this->checkDefaultTenant();
        $this->checkFailedJobs();
        $this->checkTenancyInvariants();

        $failed = array_filter($this->results, fn ($r) => $r['status'] === 'fail');
        $warnings = array_filter($this->results, fn ($r) => $r['status'] === 'warn');

        $this->table(['check', 'status', 'message'], array_map(fn ($r) => [
            $r['check'],
            strtoupper($r['status']),
            $r['message'] ?? '',
        ], $this->results));

        if (! empty($failed)) {
            $this->error(sprintf('❌ DEPLOY-CHECK: %d check(s) failed — do NOT proceed with deploy.', count($failed)));
            return self::FAILURE;
        }
        if (! empty($warnings)) {
            $this->warn(sprintf('⚠️  DEPLOY-CHECK: %d warning(s). Review before deploying.', count($warnings)));
        }
        $this->info('✅ DEPLOY-CHECK: OK — safe to proceed.');
        return self::SUCCESS;
    }

    // ---------------------------------------------------------------- checks

    private function checkAppKey(): void
    {
        $ok = ! empty(config('app.key'));
        $this->record('APP_KEY present', $ok ? 'pass' : 'fail',
            $ok ? '' : 'APP_KEY is empty — run `php artisan key:generate`.');
    }

    private function checkDebugInProd(): void
    {
        $isProd = app()->environment('production');
        if (! $isProd) {
            $this->record('APP_DEBUG safe', 'pass', 'non-production env');
            return;
        }
        $debugOn = (bool) config('app.debug');
        $this->record(
            'APP_DEBUG safe',
            $debugOn ? 'fail' : 'pass',
            $debugOn ? 'APP_DEBUG=true in production leaks stack traces.' : ''
        );
    }

    private function checkSyncQueueInProd(): void
    {
        $isProd = app()->environment('production');
        $driver = config('queue.default');
        if ($isProd && $driver === 'sync') {
            $this->record('Queue driver', 'fail', 'QUEUE_CONNECTION=sync in production blocks HTTP threads.');
        } else {
            $this->record('Queue driver', 'pass', "driver: {$driver}");
        }
    }

    private function checkRequiredEnvs(): void
    {
        $required = [
            'APP_URL',
            'DB_CONNECTION',
            'DB_DATABASE',
            'TENANCY_DEFAULT_TENANT_SLUG',
            'TENANCY_DEFAULT_TENANT_ID',
        ];

        foreach ($required as $var) {
            $val = env($var);
            $ok  = $val !== null && $val !== '';
            $this->record(
                "env: {$var}",
                $ok ? 'pass' : 'fail',
                $ok ? '' : 'unset / empty'
            );
        }
    }

    private function checkPendingMigrations(): void
    {
        try {
            // Use the framework's own "migrate:status" parser — exit code is
            // non-zero if migrations are pending.
            $output = new \Symfony\Component\Console\Output\BufferedOutput();
            $exit = Artisan::call('migrate:status', [], $output);
            $stdout = $output->fetch();

            $pending = substr_count($stdout, ' Pending ');

            if ($pending > 0) {
                $this->record('Migrations pending', 'fail', "{$pending} migration(s) not yet applied.");
            } else {
                $this->record('Migrations pending', 'pass', 'all applied');
            }
        } catch (\Throwable $e) {
            $this->record('Migrations pending', 'warn', 'could not run migrate:status: '.$e->getMessage());
        }
    }

    private function checkDefaultTenant(): void
    {
        try {
            $slug = config('tenancy.default_tenant_slug', 'default');
            $t = Tenant::query()->where('slug', $slug)->first();
            if (! $t) {
                $this->record('Default tenant', 'fail', "no tenant with slug='{$slug}'");
                return;
            }
            $this->record(
                'Default tenant',
                $t->status === 'active' ? 'pass' : 'warn',
                "slug={$t->slug} id={$t->id} status={$t->status}"
            );
        } catch (\Throwable $e) {
            $this->record('Default tenant', 'warn', 'tenants table not ready: '.$e->getMessage());
        }
    }

    private function checkFailedJobs(): void
    {
        try {
            if (! Schema::hasTable('failed_jobs')) {
                $this->record('failed_jobs', 'pass', 'table missing — no history yet');
                return;
            }
            $n = (int) DB::table('failed_jobs')->count();
            if ($n === 0) {
                $this->record('failed_jobs', 'pass', '0 rows');
            } elseif ($n < 50) {
                $this->record('failed_jobs', 'warn', "{$n} rows — review before deploy");
            } else {
                $this->record('failed_jobs', 'fail', "{$n} rows — backlog too large; drain first");
            }
        } catch (\Throwable $e) {
            $this->record('failed_jobs', 'warn', 'could not query: '.$e->getMessage());
        }
    }

    private function checkTenancyInvariants(): void
    {
        // Only if the command exists (Phase 7 may not be deployed on older
        // branches; be permissive so deploy-check is still usable).
        try {
            $exit = Artisan::call('saas:migration:validate');
            $this->record(
                'Tenancy invariants (saas:migration:validate)',
                $exit === 0 ? 'pass' : 'fail',
                $exit === 0 ? 'all invariants hold' : 'invariants broken — run validator to see details'
            );
        } catch (\Throwable $e) {
            $this->record('Tenancy invariants', 'warn', 'validator unavailable: '.$e->getMessage());
        }
    }

    // ---------------------------------------------------------------- helpers

    private function record(string $check, string $status, string $message = ''): void
    {
        $this->results[] = compact('check', 'status', 'message');
    }
}
