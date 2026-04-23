<?php

namespace App\Enums\Hr\Employee;


enum TrialPeriod: string
{
    case ZeroDays   = '0';    // 0 يوم
    case NinetyDays = '90';   // 90 يوم
    case OneEightyDays = '180'; // 180 يوم

    public function label(): string
    {
        return match ($this) {
            self::ZeroDays      => '0 يوم',
            self::NinetyDays    => '90 يوم',
            self::OneEightyDays => '180 يوم',
        };
    }

    // تعيد جميع الخيارات على شكل مصفوفة تحتوي على id و name
    public static function options(): array
    {
        return array_map(
            fn(self $t) => ['id' => $t->value, 'name' => $t->label()],
            self::cases()
        );
    }

    // تعيد جميع القيم الممكنة فقط
    public static function values(): array
    {
        return array_map(fn(self $t) => $t->value, self::cases());
    }
}
