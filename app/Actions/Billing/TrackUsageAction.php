<?php

declare(strict_types=1);

namespace App\Actions\Billing;

use App\Enums\Billing\UsageResetPeriod;
use App\Models\TenantUsage;
use App\Services\Billing\FeatureResolver;
use App\Services\Billing\UsagePeriodCalculator;
use App\Tenancy\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Atomically increments a tenant's consumption counter for a metered feature.
 * Handles period rollover transparently.
 *
 * Contract:
 *   - Acquires a row-level lock (SELECT ... FOR UPDATE) to be race-safe across
 *     queue workers and concurrent HTTP requests.
 *   - If the row doesn't exist, inserts one with period_started_at=now and
 *     reset_at=Feature::reset_period->nextResetFrom(now).
 *   - If reset_at has passed, resets used=0 and recalculates reset_at BEFORE
 *     applying the new amount. Consumers see this as "the new period started
 *     during this call".
 *
 * This action does NOT check limits. Pair it with CheckFeatureLimitAction
 * BEFORE the real work; call TrackUsageAction AFTER the work succeeds.
 *
 *   $check = app(CheckFeatureLimitAction::class)->allow('api.calls');
 *   performTheWork();
 *   app(TrackUsageAction::class)('api.calls', 1);
 */
final class TrackUsageAction
{
    public function __construct(
        private FeatureResolver $resolver,
        private UsagePeriodCalculator $calculator,
    ) {}

    public function __invoke(string $featureKey, int $amount = 1, ?int $tenantId = null): TenantUsage
    {
        if ($amount < 0) {
            throw new RuntimeException('TrackUsageAction amount must be ≥ 0.');
        }

        $tenantId ??= TenantContext::currentId();

        if ($tenantId === null) {
            throw new RuntimeException('TrackUsageAction: no tenant in context.');
        }

        $feature = $this->resolver->feature($tenantId, $featureKey);
        $period  = isset($feature['reset_period'])
            ? UsageResetPeriod::from($feature['reset_period'])
            : UsageResetPeriod::Never;

        $now = CarbonImmutable::now();

        return DB::transaction(function () use ($tenantId, $featureKey, $amount, $period, $now) {
            /** @var TenantUsage|null $row */
            $row = TenantUsage::withoutTenancy()
                ->where('tenant_id', $tenantId)
                ->where('feature_key', $featureKey)
                ->lockForUpdate()
                ->first();

            if (! $row) {
                $row = new TenantUsage([
                    'feature_key'       => $featureKey,
                    'used'              => 0,
                    'period_started_at' => $this->calculator->periodStart($period, $now),
                    'reset_at'          => $this->calculator->nextResetAt($period, $now),
                ]);
                $row->tenant_id = $tenantId;
                $row->save();
            }

            // Period rollover: zero the counter before adding.
            if ($row->isExpired()) {
                $row->used              = 0;
                $row->period_started_at = $this->calculator->periodStart($period, $now);
                $row->reset_at          = $this->calculator->nextResetAt($period, $now);
            }

            $row->used            += $amount;
            $row->last_tracked_at  = $now;
            $row->save();

            return $row;
        });
    }
}
