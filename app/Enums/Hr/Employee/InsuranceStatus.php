<?php

namespace App\Enums\Hr\Employee;

enum InsuranceStatus: string
{
    case Added             = 'added';             // مضاف
    case InformallyAdded   = 'informally_added';  // مضاف غير رسمي
    case Excluded          = 'excluded';          // مستبعد
    case NotRegistered     = 'not_registered';    // غير مسجل

    public function label(): string
    {
        return match ($this) {
            self::Added           => 'مضاف',
            self::InformallyAdded => 'مضاف غير رسمي',
            self::Excluded        => 'مستبعد',
            self::NotRegistered   => 'غير مسجل',
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
