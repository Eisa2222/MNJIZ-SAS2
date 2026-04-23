<?php

declare(strict_types=1);

namespace App\Enums\Billing;

/**
 * Canonical subscription lifecycle states.
 *
 * Transitions (enforced by Actions in app/Actions/Billing/Subscription):
 *
 *   Initial → Trialing   (StartTrialAction, when plan.trial_days > 0)
 *   Initial → Active     (ActivateSubscriptionAction, when trial_days = 0 + first payment succeeds)
 *   Trialing → Active    (first successful charge after trial_ends_at)
 *   Trialing → Canceled  (user cancels before trial ends)
 *   Active → PastDue     (MarkPastDueAction — renewal charge failed; grace begins)
 *   Active → Canceled    (CancelSubscriptionAction — remains usable until period_end)
 *   Active → Paused      (admin freeze — rare, manual)
 *   PastDue → Active     (retry succeeded)
 *   PastDue → Expired    (grace ended without payment)
 *   Canceled → Expired   (period_end passed)
 *   Paused → Active      (admin resume)
 *   * → Expired          (terminal — tenant drops to Free plan)
 */
enum SubscriptionStatus: string
{
    case Trialing = 'trialing';
    case Active   = 'active';
    case PastDue  = 'past_due';
    case Paused   = 'paused';
    case Canceled = 'canceled';
    case Expired  = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Trialing => 'Trial',
            self::Active   => 'Active',
            self::PastDue  => 'Past Due (grace)',
            self::Paused   => 'Paused',
            self::Canceled => 'Canceled (serviced until period end)',
            self::Expired  => 'Expired',
        };
    }

    /** Grants access to the plan's features. */
    public function isEntitling(): bool
    {
        return in_array($this, [
            self::Trialing, self::Active, self::PastDue, self::Canceled,
        ], true);
    }

    public function isTerminal(): bool
    {
        return $this === self::Expired;
    }
}
