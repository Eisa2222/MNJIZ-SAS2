<?php

declare(strict_types=1);

namespace App\Actions\Billing;

use App\DTOs\Billing\LimitCheckResult;
use App\Enums\Billing\FeatureType;
use App\Enums\Billing\UsageResetPeriod;
use App\Exceptions\Billing\UsageLimitExceededException;
use App\Models\TenantUsage;
use App\Services\Billing\FeatureResolver;
use App\Tenancy\TenantContext;

/**
 * Evaluates whether a tenant may consume N more units of a limit/metered
 * feature without breaching their plan.
 *
 *   - Limit   (e.g. max_users)        → compares $usedOverride (required) against limit
 *                                       ↳ caller must pass current resource count
 *   - Metered (e.g. api.calls/month)  → reads tenant_usages.used, auto-aware of reset_at
 *   - Boolean                         → returns denied (use CheckFeatureAccessAction instead)
 *
 * Usage for a HARD LIMIT (count is authoritative, not counter-based):
 *   $currentUserCount = User::count();
 *   $r = app(CheckFeatureLimitAction::class)('max_users', amount: 1, usedOverride: $currentUserCount);
 *   if (! $r->allowed) abort(403, $r->reason);
 *
 * Usage for METERED:
 *   $r = app(CheckFeatureLimitAction::class)('api.calls', amount: 1);
 *   // then: app(TrackUsageAction::class)('api.calls', 1) after the action succeeds.
 */
final class CheckFeatureLimitAction
{
    public function __construct(private FeatureResolver $resolver) {}

    public function __invoke(
        string $featureKey,
        int $amount = 1,
        ?int $tenantId = null,
        ?int $usedOverride = null,
    ): LimitCheckResult {
        $tenantId ??= TenantContext::currentId();

        if ($tenantId === null) {
            return LimitCheckResult::denied($featureKey, 'No tenant in context.');
        }

        $feature = $this->resolver->feature($tenantId, $featureKey);

        if ($feature === null) {
            return LimitCheckResult::denied($featureKey, 'Feature is not included in the current plan.');
        }

        if ($feature['type'] === FeatureType::Boolean->value) {
            return (bool) $feature['value']
                ? LimitCheckResult::unlimited($featureKey)
                : LimitCheckResult::denied($featureKey, 'Feature is disabled on the current plan.');
        }

        if (! empty($feature['unlimited'])) {
            $used = $usedOverride ?? $this->currentUsage($tenantId, $featureKey);

            return LimitCheckResult::unlimited($featureKey, used: $used);
        }

        $limit = (int) ($feature['value'] ?? 0);

        // Live count for Limit features; counter for Metered.
        $used = $usedOverride
            ?? ($feature['type'] === FeatureType::Metered->value
                ? $this->currentUsage($tenantId, $featureKey)
                : 0);

        $resetAt = $this->resetAtFor($tenantId, $featureKey, $feature);

        // Consider the requested amount: would adding $amount still fit?
        $projectedRemaining = max(0, $limit - ($used + max(0, $amount - 1)));
        $allowed            = ($used + $amount) <= $limit && $limit > 0;

        return new LimitCheckResult(
            featureKey: $featureKey,
            allowed:    $allowed,
            used:       $used,
            limit:      $limit,
            unlimited:  false,
            remaining:  $projectedRemaining,
            resetAt:    $resetAt,
            reason:     $allowed ? '' : "Limit reached ({$used}/{$limit}).",
        );
    }

    public function allow(
        string $featureKey,
        int $amount = 1,
        ?int $tenantId = null,
        ?int $usedOverride = null,
    ): LimitCheckResult {
        $result = $this->__invoke($featureKey, $amount, $tenantId, $usedOverride);

        if (! $result->allowed) {
            throw new UsageLimitExceededException($result);
        }

        return $result;
    }

    private function currentUsage(int $tenantId, string $featureKey): int
    {
        $row = TenantUsage::withoutTenancy()
            ->where('tenant_id', $tenantId)
            ->where('feature_key', $featureKey)
            ->first();

        if (! $row) {
            return 0;
        }

        // If the period has rolled over since the last tick, the counter
        // effectively reads zero for decision purposes — the persisted reset
        // happens inside TrackUsageAction to keep writes idempotent here.
        return $row->isExpired() ? 0 : $row->used;
    }

    private function resetAtFor(int $tenantId, string $featureKey, array $feature): ?\Carbon\CarbonInterface
    {
        if (($feature['reset_period'] ?? UsageResetPeriod::Never->value) === UsageResetPeriod::Never->value) {
            return null;
        }

        $row = TenantUsage::withoutTenancy()
            ->where('tenant_id', $tenantId)
            ->where('feature_key', $featureKey)
            ->first();

        return $row?->reset_at;
    }
}
