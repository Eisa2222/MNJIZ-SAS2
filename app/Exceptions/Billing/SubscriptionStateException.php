<?php

declare(strict_types=1);

namespace App\Exceptions\Billing;

use App\Enums\Billing\SubscriptionStatus;
use RuntimeException;

/**
 * Thrown when an Action attempts an illegal state transition on a subscription.
 * E.g., canceling an already-expired sub, or activating a canceled one.
 */
class SubscriptionStateException extends RuntimeException
{
    public function __construct(
        public readonly SubscriptionStatus $current,
        public readonly string $attemptedTransition,
        ?string $message = null,
    ) {
        parent::__construct(
            $message ?? "Illegal subscription transition: '{$attemptedTransition}' from state '{$current->value}'."
        );
    }
}
