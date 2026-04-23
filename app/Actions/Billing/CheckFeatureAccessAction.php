<?php

declare(strict_types=1);

namespace App\Actions\Billing;

use App\Exceptions\Billing\FeatureNotEnabledException;
use App\Services\Billing\FeatureGate;
use App\Tenancy\TenantContext;

/**
 * Cheap yes/no gate for boolean features ("does this tenant have legal_ai?").
 *
 * For limit / metered features, use CheckFeatureLimitAction — it returns a
 * quantitative DTO so the caller can show "3 of 10 used" style UI.
 *
 * Usage:
 *   app(CheckFeatureAccessAction::class)('legal_ai')              → bool
 *   app(CheckFeatureAccessAction::class)->allow('legal_ai')       → void (throws if denied)
 */
final class CheckFeatureAccessAction
{
    public function __construct(private FeatureGate $gate) {}

    public function __invoke(string $featureKey, ?int $tenantId = null): bool
    {
        return $this->gate->hasAccess($tenantId ?? TenantContext::currentId(), $featureKey);
    }

    public function allow(string $featureKey, ?int $tenantId = null): void
    {
        if (! $this->__invoke($featureKey, $tenantId)) {
            throw new FeatureNotEnabledException($featureKey, $tenantId ?? TenantContext::currentId());
        }
    }
}
