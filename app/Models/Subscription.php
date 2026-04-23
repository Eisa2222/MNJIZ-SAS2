<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Billing\BillingCycle;
use App\Enums\Billing\PaymentGateway;
use App\Enums\Billing\SubscriptionStatus;
use App\Tenancy\Concerns\BelongsToTenant;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A tenant's active (or historical) subscription to a Plan.
 *
 * @property int $tenant_id
 * @property int $plan_id
 * @property SubscriptionStatus $status
 * @property BillingCycle $billing_cycle
 * @property PaymentGateway $gateway
 * @property \Illuminate\Support\Carbon|null $trial_ends_at
 * @property \Illuminate\Support\Carbon|null $current_period_started_at
 * @property \Illuminate\Support\Carbon|null $current_period_ends_at
 * @property \Illuminate\Support\Carbon|null $canceled_at
 * @property \Illuminate\Support\Carbon|null $ends_at
 * @property \Illuminate\Support\Carbon|null $grace_ends_at
 */
class Subscription extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'plan_id', 'coupon_id',
        'status', 'billing_cycle', 'currency',
        'gateway', 'gateway_subscription_id',
        'trial_ends_at', 'current_period_started_at', 'current_period_ends_at',
        'canceled_at', 'ends_at', 'grace_ends_at',
        'meta',
    ];

    protected $casts = [
        'status'                    => SubscriptionStatus::class,
        'billing_cycle'             => BillingCycle::class,
        'gateway'                   => PaymentGateway::class,
        'trial_ends_at'             => 'datetime',
        'current_period_started_at' => 'datetime',
        'current_period_ends_at'    => 'datetime',
        'canceled_at'               => 'datetime',
        'ends_at'                   => 'datetime',
        'grace_ends_at'             => 'datetime',
        'meta'                      => 'array',
    ];

    // -------------------------------------------------- relations
    public function plan(): BelongsTo                      { return $this->belongsTo(Plan::class); }
    public function coupon(): BelongsTo                    { return $this->belongsTo(Coupon::class); }
    public function invoices(): HasMany                    { return $this->hasMany(Invoice::class); }
    public function tenantRelation(): BelongsTo            { return $this->belongsTo(Tenant::class, 'tenant_id'); }

    // -------------------------------------------------- state helpers
    public function isActive(): bool
    {
        return $this->status === SubscriptionStatus::Active
            || $this->status === SubscriptionStatus::Trialing;
    }

    public function isEntitling(): bool
    {
        return $this->status->isEntitling()
            && (! $this->ends_at || $this->ends_at->isFuture());
    }

    public function onTrial(): bool
    {
        return $this->status === SubscriptionStatus::Trialing
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    public function inGracePeriod(): bool
    {
        return $this->status === SubscriptionStatus::PastDue
            && $this->grace_ends_at
            && $this->grace_ends_at->isFuture();
    }

    public function renewsAt(): ?CarbonImmutable
    {
        return $this->current_period_ends_at?->toImmutable();
    }
}
