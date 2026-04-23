<?php

namespace App\Enums\Hr\Employee;

enum LicenseType: string
{
    case LawyerTrainee = 'trainee_lawyer';  // رخصة محامي متدرب
    case Lawyer        = 'lawyer';          // رخصة محامي
    case NoLicense     = 'no_license';      // لا يحتاج رخصة

    public function label(): string
    {
        return match ($this) {
            self::LawyerTrainee => 'رخصة محامي متدرب',
            self::Lawyer        => 'رخصة محامي',
            self::NoLicense     => 'لا يحتاج رخصة',
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