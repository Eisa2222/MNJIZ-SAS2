<?php

declare(strict_types=1);

namespace App\DTOs\Billing;

use Carbon\CarbonInterface;

/**
 * Returned by CheckFeatureLimitAction. Self-contained so callers never need to
 * re-query the DB to decide whether to proceed.
 *
 * Typical use:
 *   $result = app(CheckFeatureLimitAction::class)('max_users');
 *   if (! $result->allowed) abort(403);
 */
final class LimitCheckResult
{
    public function __construct(
        public readonly string $featureKey,
        public readonly bool $allowed,
        public readonly int $used,
        public readonly ?int $limit,           // null = feature not configured for this plan
        public readonly bool $unlimited,       // true if plan grants unlimited usage
        public readonly ?int $remaining,       // null if unlimited
        public readonly ?CarbonInterface $resetAt,
        public readonly string $reason = '',   // human-readable explanation when !allowed
    ) {}

    public static function denied(string $featureKey, string $reason = ''): self
    {
        return new self(
            featureKey: $featureKey,
            allowed:    false,
            used:       0,
            limit:      0,
            unlimited:  false,
            remaining:  0,
            resetAt:    null,
            reason:     $reason,
        );
    }

    public static function unlimited(string $featureKey, int $used = 0): self
    {
        return new self(
            featureKey: $featureKey,
            allowed:    true,
            used:       $used,
            limit:      null,
            unlimited:  true,
            remaining:  null,
            resetAt:    null,
        );
    }

    public static function withinLimit(
        string $featureKey,
        int $used,
        int $limit,
        ?CarbonInterface $resetAt = null,
    ): self {
        $remaining = max(0, $limit - $used);

        return new self(
            featureKey: $featureKey,
            allowed:    $remaining > 0,
            used:       $used,
            limit:      $limit,
            unlimited:  false,
            remaining:  $remaining,
            resetAt:    $resetAt,
            reason:     $remaining > 0 ? '' : 'Limit reached.',
        );
    }

    public function toArray(): array
    {
        return [
            'feature_key' => $this->featureKey,
            'allowed'     => $this->allowed,
            'used'        => $this->used,
            'limit'       => $this->limit,
            'unlimited'   => $this->unlimited,
            'remaining'   => $this->remaining,
            'reset_at'    => $this->resetAt?->toIso8601String(),
            'reason'      => $this->reason,
        ];
    }
}
