<?php

namespace App\Enums\Hr\Employee;

enum QualificationDegree: string
{
    case Secondary  = 'secondary';          // ثانوي
    case Diploma    = 'diploma';            // دبلوم
    case Bachelor   = 'bachelor';           // بكالوريوس
    case Master     = 'master';            // ماجستير
    case Doctorate  = 'doctorate';          // دكتوراه

    public function label(): string
    {
        return match ($this) {
            self::Secondary     => 'ثانوي',
            self::Diploma       => 'دبلوم',
            self::Bachelor      => 'بكالوريوس',
            self::Master        => 'ماجستير',
            self::Doctorate     => 'دكتوراه',
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