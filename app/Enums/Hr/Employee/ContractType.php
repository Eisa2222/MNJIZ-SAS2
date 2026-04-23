<?php

namespace App\Enums\Hr\Employee;

enum ContractType: string
{
    case Specific     = 'specific';     // محدد
    case NonSpecific  = 'non_specific'; // غير محدد

    public function label(): string
    {
        return match ($this) {
            self::Specific     => 'محدد',
            self::NonSpecific  => 'غير محدد',
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
