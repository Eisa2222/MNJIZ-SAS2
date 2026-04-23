<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Enums\Billing\FeatureType;
use App\Enums\Billing\UsageResetPeriod;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;

/**
 * Reads a tenant's resolved feature set (plan + pivot values) and caches the
 * whole map. Cache key:  tenant_{id}_plan_features
 *
 * Cached shape (array keyed by feature.key):
 *   [
 *     'max_users' => [
 *       'feature_id'   => 3,
 *       'type'         => 'limit',
 *       'reset_period' => 'never',
 *       'unit'         => null,
 *       'value'        => 10,          // int | bool | null(=unlimited)
 *       'unlimited'    => false,
 *     ],
 *     ...
 *   ]
 *
 * Callers MUST NOT modify DB without invalidating via forgetFor($tenantId)
 * or flushAll(). AssignPlanToTenantAction + PlanObserver (future) handle
 * invalidation on every write path that matters.
 */
final class FeatureResolver
{
    public function resolve(int $tenantId): array
    {
        $key = sprintf(
            config('billing.cache.tenant_key_format', 'tenant_%d_plan_features'),
            $tenantId
        );
        $ttl = (int) config('billing.cache.ttl_seconds', 600);

        return Cache::remember($key, $ttl, fn () => $this->loadFromDatabase($tenantId));
    }

    public function forTenant(Tenant $tenant): array
    {
        return $this->resolve($tenant->getKey());
    }

    public function feature(int $tenantId, string $featureKey): ?array
    {
        $map = $this->resolve($tenantId);

        return $map[$featureKey] ?? null;
    }

    public function forgetFor(int $tenantId): void
    {
        $key = sprintf(
            config('billing.cache.tenant_key_format', 'tenant_%d_plan_features'),
            $tenantId
        );
        Cache::forget($key);
    }

    /**
     * Blast all tenants' caches — use sparingly (e.g. from a plan admin UI
     * button, or PlanObserver on plan-wide value changes).
     */
    public function flushAllTenantsOnPlan(int $planId): void
    {
        Tenant::query()
            ->where('plan_id', $planId)
            ->pluck('id')
            ->each(fn ($id) => $this->forgetFor((int) $id));
    }

    private function loadFromDatabase(int $tenantId): array
    {
        $tenant = Tenant::query()->find($tenantId);

        if (! $tenant || ! $tenant->hasPlan()) {
            return [];
        }

        /** @var Plan|null $plan */
        $plan = Plan::query()
            ->with('features') // features via plan_features pivot, with pivot cols
            ->find($tenant->plan_id);

        if (! $plan || ! $plan->is_active) {
            return [];
        }

        $map = [];
        $unlimitedSentinel = config('billing.unlimited_sentinel', '__unlimited__');

        /** @var Feature $feature */
        foreach ($plan->features as $feature) {
            $raw = $feature->pivot->value;
            $type = $feature->type;

            $unlimited = false;
            $value     = null;

            if ($type === FeatureType::Boolean) {
                $value = in_array($raw, ['1', 'true', 'yes', 'on'], true);
            } else {
                if ($raw === null) {
                    $value = 0;
                } elseif ($raw === $unlimitedSentinel || $raw === '-1') {
                    $value     = null;
                    $unlimited = true;
                } else {
                    $value = (int) $raw;
                }
            }

            $map[$feature->key] = [
                'feature_id'   => $feature->id,
                'type'         => $type->value,
                'reset_period' => $feature->reset_period instanceof UsageResetPeriod
                                    ? $feature->reset_period->value
                                    : UsageResetPeriod::Never->value,
                'unit'         => $feature->unit,
                'value'        => $value,
                'unlimited'    => $unlimited,
            ];
        }

        return $map;
    }
}
