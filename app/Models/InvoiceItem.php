<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Line items belong to Invoice which is tenant-scoped — so items are
 * indirectly isolated through FK. No BelongsToTenant trait needed.
 */
class InvoiceItem extends Model
{
    use HasFactory;

    protected $table = 'billing_invoice_items';

    protected $fillable = [
        'invoice_id', 'description', 'quantity', 'unit_price', 'amount', 'meta',
    ];

    protected $casts = [
        'quantity'   => 'int',
        'unit_price' => 'decimal:2',
        'amount'     => 'decimal:2',
        'meta'       => 'array',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
