<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Billing\PaymentGateway;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Central entity (no BelongsToTenant) — events arrive BEFORE we know the
 * tenant; correlation happens during processing and the FK is filled in
 * when known.
 */
class WebhookEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'gateway', 'event_id', 'event_type',
        'tenant_id', 'payment_id',
        'payload', 'signature', 'verified',
        'received_at', 'processed_at', 'error_message', 'retry_count',
    ];

    protected $casts = [
        'gateway'      => PaymentGateway::class,
        'payload'      => 'array',
        'verified'     => 'bool',
        'received_at'  => 'datetime',
        'processed_at' => 'datetime',
        'retry_count'  => 'int',
    ];

    public function tenant(): BelongsTo   { return $this->belongsTo(Tenant::class); }
    public function payment(): BelongsTo  { return $this->belongsTo(Payment::class)->withoutGlobalScopes(); }

    public function isProcessed(): bool
    {
        return $this->processed_at !== null;
    }

    public function markProcessed(): void
    {
        $this->forceFill(['processed_at' => now(), 'error_message' => null])->save();
    }

    public function markFailed(string $error): void
    {
        $this->forceFill([
            'error_message' => $error,
            'retry_count'   => $this->retry_count + 1,
        ])->save();
    }
}
