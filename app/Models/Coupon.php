<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Billing\CouponDuration;
use App\Enums\Billing\CouponType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Central entity — coupons are platform-wide promotions, not tenant-scoped.
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
