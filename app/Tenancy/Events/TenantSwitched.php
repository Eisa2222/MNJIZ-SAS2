<?php

declare(strict_types=1);

namespace App\Tenancy\Events;

use App\Models\Tenant;

final class TenantSwitched
{
    public function __construct(
        public readonly ?Tenant $current,
        public readonly ?Tenant $previous,
    ) {}
}
