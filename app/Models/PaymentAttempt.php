<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Scoped via Payment.tenant_id (no trait needed — FK-based isolation).
 */
class PaymentAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id', 'status', 'gateway_event_id',
        'error_message', 'gateway_response', 'attempted_at',
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'attempted_at'     => 'datetime',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
