<?php

namespace App\Enums\ElectronicServices\Custody\Log;

enum CustodyLogAction: string
{
    case Checkout = 'checkout';
    case Checkin  = 'checkin';
    case CheckoutCanceled = 'checkout_canceled'; // إلغاء إسناد العهدة
    case CheckinCanceled = 'checkin_canceled';   // إلغاء إرجاع العهدة


    public function label(): string
    {
        return match ($this) {
            self::Checkout => 'تسليم',
            self::Checkin  => 'إرجاع',
            self::CheckoutCanceled => 'إلغاء إسناد العهدة',
            self::CheckinCanceled => 'إلغاء إرجاع العهدة',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn(self $t) => ['id' => $t->value, 'name' => $t->label()],
            self::cases()
        );
    }

    public function color(): string
    {
        return match ($this) {
            self::Checkout => 'primary',
            self::Checkin  => 'success',
            self::CheckoutCanceled => 'warning',
            self::CheckinCanceled => 'warning',
        };
    }
}
