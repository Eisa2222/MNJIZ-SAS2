<?php

declare(strict_types=1);

namespace App\Events\Billing;

use App\Models\Subscription;
use Illuminate\Foundation\Events\Dispatchable;

class SubscriptionCanceled
{
    use Dispatchable;

    public function __construct(
        public readonly Subscription $subscription,
        public readonly bool $immediate = false,
    ) {}
}
