<?php

declare(strict_types=1);

namespace App\Listeners\Billing;

use App\Events\Billing\SubscriptionActivated;
use App\Events\Billing\SubscriptionCanceled;
use App\Events\Billing\SubscriptionExpired;
use App\Services\Billing\FeatureResolver;

/**
 * Belt-and-braces: AssignPlanToTenantAction already flushes the cache, but if
 * any future Action updates a subscription WITHOUT going through that path
 * (e.g. a raw admin edit) the feature resolver must still drop stale reads.
 */
final class FlushFeatureCacheOnSubscriptionChange
{
    public function __construct(private FeatureResolver $resolver) {}

    public function handle(SubscriptionActivated|SubscriptionCanceled|SubscriptionExpired $event): void
    {
        $this->resolver->forgetFor($event->subscription->tenant_id);
    }
}
