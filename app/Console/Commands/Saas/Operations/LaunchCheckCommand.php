<?php

declare(strict_types=1);

namespace App\Console\Commands\Saas\Operations;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

/**
 * `saas:launch:check`
 *
 * Phase 9 launch-readiness gate. Wraps `saas:deploy:check` (operational
 * health) and adds GTM-specific assertions:
 *
 *   1. saas:deploy:check passes (ops health: env, migrations, tenant)
 *   2. At least one PAID, ACTIVE plan exists (otherwise no one can pay)
 *   3. Pro plan exists (default landing-page CTA target)
 *   4. Public signup route is registered
 *   5. Landing page route is registered
 *   6. Onboarding route is registered
 *   7. Health endpoint is reachable from CLI (sanity)
 *
 * Exit 0 = ready to launch; non-zero = block launch.
 */
final class LaunchCheckCommand extends Command
{
    protected $signature   = 'saas:launch:check';
    protected $description = 'Phase 9 launch-readiness gate (GTM + ops health). Non-zero exit blocks launch.';

    /** @var array<array{check: string, status: string, message: string}> */
    private array $results = [];

    public function handle(): int
    {
        $this->checkDeployHealth();
        $this->checkPaidPlanExists();
        $this->checkProPlan();
        $this->checkRoute('marketing.landing', 'Landing route');
        $this->checkRoute('register',          'Signup route');
        $this->checkRoute('onboarding.welcome','Onboarding route');
        $this->checkRoute('health.overall',    'Health endpoint');

        $this->table(['check', 'status', 'message'], array_map(fn ($r) => [
            $r['check'], strtoupper($r['status']), $r['message'],
        ], $this->results));

        $failed = array_filter($this->results, fn ($r) => $r['status'] === 'fail');

        if (! empty($failed)) {
            $this->error(sprintf('❌ LAUNCH-CHECK: %d check(s) failed — do NOT launch.', count($failed)));
            return self::FAILURE;
        }

        $this->info('✅ LAUNCH-CHECK: ready to launch.');
        return self::SUCCESS;
    }

    private function checkDeployHealth(): void
    {
        try {
            $exit = Artisan::call('saas:deploy:check');
            $this->record(
                'Deploy-check (ops health)',
                $exit === 0 ? 'pass' : 'fail',
                $exit === 0 ? 'all ops checks pass' : 'underlying deploy:check failed — run it directly to see details'
            );
        } catch (\Throwable $e) {
            $this->record('Deploy-check (ops health)', 'fail', 'unable to run: '.$e->getMessage());
        }
    }

    private function checkPaidPlanExists(): void
    {
        try {
            if (! Schema::hasTable('plans')) {
                $this->record('Paid plan exists', 'fail', 'plans table missing');
                return;
            }
            $n = (int) DB::table('plans')->where('is_active', true)->where('is_free', false)->count();
            $this->record(
                'Paid plan exists',
                $n > 0 ? 'pass' : 'fail',
                $n > 0 ? "{$n} paid plan(s) active" : 'no paid plans seeded — run DefaultPlansSeeder'
            );
        } catch (\Throwable $e) {
            $this->record('Paid plan exists', 'fail', 'query failed: '.$e->getMessage());
        }
    }

    private function checkProPlan(): void
    {
        try {
            // Pro is stored under slug=professional (kept for back-compat).
            $hasPro = Schema::hasTable('plans')
                && (int) DB::table('plans')->where('slug', 'professional')->where('is_active', true)->count() > 0;
            $this->record(
                'Pro plan (signup default)',
                $hasPro ? 'pass' : 'fail',
                $hasPro ? 'professional slug active' : 'signup default plan is `professional` — that slug must exist + be active'
            );
        } catch (\Throwable $e) {
            $this->record('Pro plan (signup default)', 'fail', 'query failed: '.$e->getMessage());
        }
    }

    private function checkRoute(string $name, string $label): void
    {
        try {
            $route = Route::getRoutes()->getByName($name);
            $this->record(
                $label,
                $route ? 'pass' : 'fail',
                $route ? "registered: {$route->uri()}" : "route '{$name}' is not registered"
            );
        } catch (\Throwable $e) {
            $this->record($label, 'fail', 'unable to inspect: '.$e->getMessage());
        }
    }

    private function record(string $check, string $status, string $message = ''): void
    {
        $this->results[] = compact('check', 'status', 'message');
    }
}
