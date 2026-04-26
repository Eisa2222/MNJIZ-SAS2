<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * V2 — Tenant subscription. Central DB.
 *
 * Spec line 146 — required methods:
 *   isActive(), isTrialing(), isExpired(), daysUntilExpiry()
 */
final class Subscription extends Model
{
    use HasFactory;

    public const STATUS_TRIALING  = 'trialing';
    public const STATUS_ACTIVE    = 'active';
    public const STATUS_PAST_DUE  = 'past_due';
    public const STATUS_CANCELED  = 'canceled';
    public const STATUS_EXPIRED   = 'expired';
    public const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'tenant_id', 'plan_id', 'billing_cycle', 'status',
        'trial_ends_at', 'starts_at', 'ends_at', 'canceled_at', 'next_billing_at',
        'amount', 'currency',
        'coupon_id', 'discount_amount',
        'metadata',
    ];

    protected $casts = [
        'trial_ends_at'   => 'datetime',
        'starts_at'       => 'datetime',
        'ends_at'         => 'datetime',
        'canceled_at'     => 'datetime',
        'next_billing_at' => 'datetime',
        'amount'          => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'metadata'        => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_TRIALING], true)
            && (! $this->ends_at || $this->ends_at->isFuture());
    }

    public function isTrialing(): bool
    {
        return $this->status === self::STATUS_TRIALING
            && $this->trial_ends_at?->isFuture() ?? false;
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED
            || ($this->ends_at && $this->ends_at->isPast());
    }

    public function daysUntilExpiry(): ?int
    {
        if (! $this->ends_at) {
            return null;
        }
        return max(0, (int) now()->diffInDays($this->ends_at, false));
    }
}
