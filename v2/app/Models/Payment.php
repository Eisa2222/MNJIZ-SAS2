<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Payment extends Model
{
    use HasFactory;

    public const STATUS_INITIATED = 'initiated';
    public const STATUS_PAID      = 'paid';
    public const STATUS_FAILED    = 'failed';
    public const STATUS_REFUNDED  = 'refunded';

    protected $fillable = [
        'tenant_id', 'subscription_id',
        'moyasar_payment_id', 'moyasar_invoice_id',
        'amount', 'currency', 'status', 'payment_method',
        'moyasar_response', 'failure_message', 'paid_at',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'moyasar_response' => 'array',
        'paid_at'          => 'datetime',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }
}
