<?php

declare(strict_types=1);

namespace App\Events\Billing;

use App\Models\Invoice;
use Illuminate\Foundation\Events\Dispatchable;

class InvoiceCreated
{
    use Dispatchable;

    public function __construct(public readonly Invoice $invoice) {}
}
