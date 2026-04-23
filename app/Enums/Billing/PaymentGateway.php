<?php

declare(strict_types=1);

namespace App\Enums\Billing;

enum PaymentGateway: string
{
    case Moyasar = 'moyasar';
    case Manual  = 'manual';        // bank transfer, cash — marked paid by admin
    case Stripe  = 'stripe';        // future
    case HyperPay = 'hyperpay';     // future

    public function label(): string
    {
        return match ($this) {
            self::Moyasar   => 'Moyasar',
            self::Manual    => 'Manual (Bank Transfer)',
            self::Stripe    => 'Stripe',
            self::HyperPay  => 'HyperPay',
        };
    }

    public function supportsWebhooks(): bool
    {
        return match ($this) {
            self::Moyasar, self::Stripe, self::HyperPay => true,
            self::Manual                                => false,
        };
    }
}
