<?php

declare(strict_types=1);

namespace App\Enums\Billing;

/**
 * Canonical feature types. Any new type MUST be added here AND handled by
 * FeatureResolver::castValue() / LimitCheckResult.
 */
enum FeatureType: string
{
    /** Binary on/off. plan_features.value = "1" | "0". */
    case Boolean = 'boolean';

    /**
     * Hard maximum (e.g. max_users = 10). plan_features.value = "<int>"
     * or "__unlimited__". Enforced via CheckFeatureLimitAction comparing to
     * the tenant's live resource count — NOT usage_tracking.
     */
    case Limit = 'limit';

    /**
     * Counter that ticks up over time (e.g. api_calls = 10000 / month).
     * Uses tenant_usages table + reset_period.
     */
    case Metered = 'metered';

    public function label(): string
    {
        return match ($this) {
            self::Boolean => 'Boolean',
            self::Limit   => 'Hard Limit',
            self::Metered => 'Metered Usage',
        };
    }

    public function usesCounter(): bool
    {
        return $this === self::Metered;
    }
}
