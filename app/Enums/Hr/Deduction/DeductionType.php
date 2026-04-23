<?php

namespace App\Enums\Hr\Deduction;

enum DeductionType: string
{
    case Violation      = 'violation';        // مخالفة
    case AdvancePay     = 'advance_pay';      // استقطاع سلفة
    case OtherPayments  = 'other_payments';   // مدفوعات أخرى
    case Other          = 'other';            // أخرى



    public function label(): string
    {
        return match ($this) {
            self::Violation     => 'مخالفة',
            self::AdvancePay    => 'استقطاع سلفة',
            self::OtherPayments => 'مدفوعات أخرى',
            self::Other         => 'أخرى',
        };
    }


    public static function options(): array
    {
        return array_map(
            fn(self $t) => ['id' => $t->value, 'name' => $t->label()],
            self::cases()
        );
    }

    // تعيد جميع القيم الممكنة للنوع
    public static function values(): array
    {
        return array_map(fn(self $t) => $t->value, self::cases());
    }
}
