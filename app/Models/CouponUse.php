<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Phase E — per-redemption record for coupons.
 *
 * Created by `CouponService::apply()` inside the same transaction as the
 * atomic `coupons.redemptions_count` increment. Acts as an audit trail
 * + analytics source — Phase 5 callers don't need to know about it.
 *
 * Central-only (no BelongsToTenant trait). The `tenant_id` FK lets us
 * join back to the redeeming tenant for usage reports.
 *
 * @property int $id
 * @property int $coupon_id
 * @property int|null $tenant_id
 * @property int|null $subscription_id
 * @property float $discount_amount
 * @property string $currency
 * @property \Illuminate\Support\Carbon $used_at
 */
final class CouponUse extends Model
{
    use HasFactory;

    protected $table = 'coupon_uses';

    protected $fillable = [
        'coupon_id',
        'tenant_id',
        'subscription_id',
        'discount_amount',
        'currency',
        'used_at',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'used_at'         => 'datetime',
    ];

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
