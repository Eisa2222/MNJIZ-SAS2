<?php

namespace App\Enums\Hr\Advance;

enum AdvanceType: string
{
    case Cash             = 'cash';
    case SalaryDeduction  = 'salary_deduction';


    public function label(): string
    {
        return match ($this) {
            self::Cash              => 'نقدي',
            self::SalaryDeduction   => 'استقطاع من الراتب',
        };
    }


    public static function options(): array
    {
        return array_map(
            fn(self $type) => ['id' => $type->value, 'name' => $type->label()],
            self::cases()
        );
    }

    // تعيد جميع القيم الممكنة للنوع
    public static function values(): array
    {
        return array_map(fn(self $t) => $t->value, self::cases());
    }
}
