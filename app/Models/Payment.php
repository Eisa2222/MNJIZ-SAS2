<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Billing\PaymentGateway;
use App\Enums\Billing\PaymentStatus;
use App\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'invoice_id', 'status',
        'amount', 'amount_refunded', 'currency',
        'gateway', 'gateway_payment_id',
        'card_last4', 'card_brand', 'source_type',
        'paid_at', 'failed_at', 'failure_reason', 'meta',
    ];

    protected $casts = [
        'status'          => PaymentStatus::class,
        'gateway'         => PaymentGateway::class,
        'amount'          => 'decimal:2',
        'amount_refunded' => 'decimal:2',
        'paid_at'         => 'datetime',
        'failed_at'       => 'datetime',
        'meta'            => 'array',
    ];

    public function invoice(): BelongsTo   { return $this->belongsTo(Invoice::class); }
    public function attempts(): HasMany    { return $this->hasMany(PaymentAttempt::class); }
    public function refunds(): HasMany     { return $this->hasMany(Refund::class); }

    public function isRefundable(): bool
    {
        return $this->status === PaymentStatus::Captured
            && (float) $this->amount_refunded < (float) $this->amount;
    }

    public function remainingRefundable(): float
    {
        return max(0, (float) $this->amount - (float) $this->amount_refunded);
    }
}
