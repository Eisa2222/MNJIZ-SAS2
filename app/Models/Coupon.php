<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Billing\CouponDuration;
use App\Enums\Billing\CouponType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Central entity — coupons are platform-wide promotions, not tenant-scoped.
 *
 * Phase E added a parallel set of compliance methods (`isValid`,
 * `calculateDiscount`, `getRemainingUses`, etc.) on top of Phase 5's
 * existing `isRedeemable` / `computeDiscount` API. The new names align
 * with the SaaS reference spec while the old ones stay so the 224-test
 * Phase 5 baseline keeps passing.
 *
 * Field naming note: Phase 5 chose `redemptions_count`. The spec uses
 * `uses_count`. Phase E exposes a `uses_count` accessor that proxies the
 * physical column so spec-shaped callers and Phase 5 callers both work.
 */
class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'description',
        'type', 'value', 'currency',
        'duration', 'duration_in_months',
        'applies_to', 'min_amount',
        'max_redemptions', 'redemptions_count',
        'redeem_by', 'is_active', 'meta',
    ];

    protected $casts = [
        'type'              => CouponType::class,
        'duration'          => CouponDuration::class,
        'value'             => 'decimal:2',
        'min_amount'        => 'decimal:2',
        'redeem_by'         => 'datetime',
        'is_active'         => 'bool',
        'max_redemptions'   => 'int',
        'redemptions_count' => 'int',
        'meta'              => 'array',
    ];

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'coupon_plan');
    }

    /**
     * Phase E — per-redemption audit trail (in addition to the atomic
     * `redemptions_count` counter on this row). Both are written by
     * CouponService::apply() inside a single DB::transaction.
     */
    public function uses(): HasMany
    {
        return $this->hasMany(CouponUse::class);
    }

    // ── Phase E spec-shaped API ────────────────────────────────────────

    /**
     * Compatibility accessor — spec calls it `uses_count`, the Phase 5
     * column is `redemptions_count`. This way tests written against the
     * spec name keep working without a schema change.
     */
    public function getUsesCountAttribute(): int
    {
        return (int) ($this->attributes['redemptions_count'] ?? 0);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isValid(): bool
    {
        return $this->is_active
            && ! $this->isExpired()
            && ! $this->hasReachedMaxUses();
    }

    public function isExpired(): bool
    {
        return $this->redeem_by !== null && $this->redeem_by->isPast();
    }

    public function hasReachedMaxUses(): bool
    {
        return $this->max_redemptions !== null
            && $this->redemptions_count >= $this->max_redemptions;
    }

    public function getRemainingUses(): ?int
    {
        if ($this->max_redemptions === null) {
            return null;
        }

        return max(0, $this->max_redemptions - $this->redemptions_count);
    }

    public function isApplicableToPlan(int $planId): bool
    {
        if ($this->applies_to !== 'specific_plans') {
            return true;
        }

        // Be defensive — both eager-loaded and lazy-loaded callers reach
        // this method, so we use the relation query rather than ->contains.
        return $this->plans()->whereKey($planId)->exists();
    }

    /**
     * Per-cycle gating is OPTIONAL. If a coupon has
     * `meta.billing_cycles = ['monthly']` it's restricted; otherwise
     * applicable to every cycle. Keeps the schema unchanged.
     */
    public function isApplicableToBillingCycle(string $cycle): bool
    {
        $allowed = $this->meta['billing_cycles'] ?? null;

        if (! is_array($allowed) || count($allowed) === 0) {
            return true;
        }

        return in_array($cycle, $allowed, true);
    }

    /**
     * Spec name for `computeDiscount`. Both call into CouponType::applyTo
     * so behaviour is identical.
     */
    public function calculateDiscount(float $amount): float
    {
        return $this->computeDiscount($amount);
    }

    // ── Phase 5 API (kept verbatim for 224-test baseline) ──────────────

    public function isRedeemable(?float $subtotal = null, ?Plan $plan = null): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->redeem_by && $this->redeem_by->isPast()) {
            return false;
        }

        if ($this->max_redemptions !== null && $this->redemptions_count >= $this->max_redemptions) {
            return false;
        }

        if ($this->min_amount !== null && $subtotal !== null && $subtotal < (float) $this->min_amount) {
            return false;
        }

        if ($this->applies_to === 'specific_plans' && $plan && ! $this->plans->contains($plan->id)) {
            return false;
        }

        return true;
    }

    public function computeDiscount(float $subtotal): float
    {
        return $this->type->applyTo($subtotal, (float) $this->value, (string) $this->currency);
    }
}
