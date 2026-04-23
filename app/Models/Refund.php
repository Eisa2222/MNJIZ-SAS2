<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Billing\RefundStatus;
use App\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'payment_id', 'tenant_id', 'status', 'amount', 'currency',
        'reason', 'gateway_refund_id', 'refunded_at', 'failure_reason', 'meta',
    ];

    protected $casts = [
        'status'      => RefundStatus::class,
        'amount'      => 'decimal:2',
        'refunded_at' => 'datetime',
        'meta'        => 'array',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
