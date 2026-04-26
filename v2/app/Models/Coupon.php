<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * V2 — Coupon. Central DB.
 *
 * Spec lines 393-417 — full method surface:
 *   isValid(), isApplicableToPlan(), isApplicableToBillingCycle(),
 *   hasReachedMaxUses(), isExpired(), calculateDiscount(),
 *   getRemainingUses(), scopeActive()
 *
 * Atomic counter: ALWAYS increment via DB::increment('uses_count') —
 * NEVER `$coupon->uses_count++` (race-condition free per spec line 548).
 */
final class Coupon extends Model
{
    use HasFactory;

    public const TYPE_PERCENTAGE = 'percentage';
    public const TYPE_FIXED      = 'fixed';

    protected $fillable = [
        'code', 'name', 'type', 'value',
        'max_uses', 'uses_count', 'min_order_amount',
        'applicable_plans', 'billing_cycles',
        'starts_at', 'expires_at',
        'is_active', 'created_by',
    ];

    protected $casts = [
        'value'             => 'decimal:2',
        'max_uses'          => 'integer',
        'uses_count'        => 'integer',
        'min_order_amount'  => 'decimal:2',
        'applicable_plans'  => 'array',
        'billing_cycles'    => 'array',
        'starts_at'         => 'datetime',
        'expires_at'        => 'datetime',
        'is_active'         => 'boolean',
    ];

    public function uses(): HasMany
    {
        return $this->hasMany(CouponUse::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(SuperAdmin::class, 'created_by');
    }

    /** Spec scope (line 415). */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->where(function ($q) {
                $q->whereNull('max_uses')
                  ->orWhereColumn('uses_count', '<', 'max_uses');
            });
    }

    public function isValid(): bool
    {
        return $this->is_active
            && ! $this->isExpired()
            && ! $this->hasReachedMaxUses()
            && (! $this->starts_at || $this->starts_at->isPast());
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function hasReachedMaxUses(): bool
    {
        return $this->max_uses !== null && $this->uses_count >= $this->max_uses;
    }

    public function isApplicableToPlan(int $planId): bool
    {
        // null applicable_plans = applies to ALL plans (spec line 134, 399)
        if ($this->applicable_plans === null || $this->applicable_plans === []) {
            return true;
        }
        return in_array($planId, $this->applicable_plans, true);
    }

    public function isApplicableToBillingCycle(string $cycle): bool
    {
        // null billing_cycles = applies to BOTH monthly + yearly
        if ($this->billing_cycles === null || $this->billing_cycles === []) {
            return true;
        }
        return in_array($cycle, $this->billing_cycles, true);
    }

    public function calculateDiscount(float $amount): float
    {
        if ($this->type === self::TYPE_PERCENTAGE) {
            return round($amount * ((float) $this->value / 100), 2);
        }
        // Fixed: cap at the amount itself so we never go below zero.
        return min($amount, (float) $this->value);
    }

    public function getRemainingUses(): ?int
    {
        if ($this->max_uses === null) {
            return null;   // unlimited
        }
        return max(0, $this->max_uses - $this->uses_count);
    }
}
