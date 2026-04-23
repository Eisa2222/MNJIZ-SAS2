<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Tenancy\TenantContext;

/**
 * Public-facing façade — shortest-path API for the rest of the codebase.
 * Everything that needs to ask "can this tenant use X?" goes through here.
 *
 * Intentionally thin — real logic lives in FeatureResolver / the Actions.
 */
final class FeatureGate
{
    public function __construct(private FeatureResolver $resolver) {}

    public function hasAccess(?int $tenantId, string $featureKey): bool
    {
        $tenantId ??= TenantContext::currentId();

        if ($tenantId === null) {
            return false;
        }

        $feature = $this->resolver->feature($tenantId, $featureKey);

        if ($feature === null) {
            return false;
        }

        return match ($feature['type']) {
            'boolean'        => (bool) $feature['value'],
            'limit', 'metered' => $feature['unlimited']
                                    || (is_int($feature['value']) && $feature['value'] > 0),
            default          => false,
        };
    }

    public function limit(?int $tenantId, string $featureKey): ?int
    {
        $tenantId ??= TenantContext::currentId();
        if ($tenantId === null) return null;

        $feature = $this->resolver->feature($tenantId, $featureKey);

        return is_int($feature['value'] ?? null) ? $feature['value'] : null;
    }

    public function isUnlimited(?int $tenantId, string $featureKey): bool
    {
        $tenantId ??= TenantContext::currentId();
        if ($tenantId === null) return false;

        $feature = $this->resolver->feature($tenantId, $featureKey);

        return (bool) ($feature['unlimited'] ?? false);
    }
}
