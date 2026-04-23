<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Billing\InvoiceStatus;
use App\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Rule (enforced by app-layer): financial rows are NEVER hard-deleted.
 * Status fields + void semantics handle cancellation. Keep audit trail intact.
 */
class Invoice extends Model
{
    use HasFactory, BelongsToTenant;

    // SaaS billing invoice — distinct from the legacy HR 'invoices' table.
    protected $table = 'billing_invoices';

    protected $fillable = [
        'tenant_id', 'subscription_id', 'coupon_id', 'tax_rate_id',
        'number', 'status',
        'subtotal', 'tax_amount', 'discount_amount', 'total',
        'amount_paid', 'amount_refunded', 'currency',
        'period_start', 'period_end', 'due_at',
        'issued_at', 'paid_at', 'voided_at',
        'pdf_path', 'notes', 'meta',
    ];

    protected $casts = [
        'status'          => InvoiceStatus::class,
        'subtotal'        => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total'           => 'decimal:2',
        'amount_paid'     => 'decimal:2',
        'amount_refunded' => 'decimal:2',
        'period_start'    => 'datetime',
        'period_end'      => 'datetime',
        'due_at'          => 'datetime',
        'issued_at'       => 'datetime',
        'paid_at'         => 'datetime',
        'voided_at'       => 'datetime',
        'meta'            => 'array',
    ];

    public function subscription(): BelongsTo { return $this->belongsTo(Subscription::class); }
    public function coupon(): BelongsTo       { return $this->belongsTo(Coupon::class); }
    public function taxRate(): BelongsTo      { return $this->belongsTo(TaxRate::class); }
    public function items(): HasMany          { return $this->hasMany(InvoiceItem::class); }
    public function payments(): HasMany       { return $this->hasMany(Payment::class); }

    public function isFullyPaid(): bool
    {
        return $this->amount_paid >= $this->total && $this->total > 0;
    }

    public function outstandingAmount(): float
    {
        return max(0, (float) $this->total - (float) $this->amount_paid);
    }
}
