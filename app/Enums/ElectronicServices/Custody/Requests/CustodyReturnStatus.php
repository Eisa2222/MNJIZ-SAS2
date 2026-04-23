<?php

namespace App\Enums\ElectronicServices\Custody\Requests;

enum CustodyReturnStatus: string
{
    case NoLongerNeeded     = 'no_longer_needed';           // لم أعد بحاجة إليها
    case JobCompleted       = 'job_completed';                // انتهاء المهمة
    case Replacement        = 'replacement';                   // استبدال بعهدة أخرى
    case Malfunction        = 'malfunction';                   // عطل/خلل فني
    case Damage             = 'damage';                             // هلا العهدة
    case Lost               = 'lost';                                 // فقدان العهدة
    case EndOfContract      = 'end_of_contract';             // انتهاء العقد
    case PersonalReason     = 'personal_reason';            // أسباب شخصية

    public function label(): string
    {
        return match ($this) {
            self::NoLongerNeeded    => 'لم أعد بحاجة إليها',
            self::JobCompleted      => 'انتهاء المهمة',
            self::Replacement       => 'استبدال بعهدة أخرى',
            self::Malfunction       => 'عطل/خلل فني',
            self::Damage            => 'هلاك العهدة',
            self::Lost              => 'فقدان العهدة',
            self::EndOfContract     => 'انتهاء العقد',
            self::PersonalReason    => 'أسباب شخصية',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn(self $t) => ['id' => $t->value, 'name' => $t->label()],
            self::cases()
        );
    }


    public function icon(): string
    {
        return match ($this) {
            self::NoLongerNeeded    => 'ti-check',
            self::JobCompleted      => 'ti-flag',
            self::Replacement       => 'ti-refresh',
            self::Malfunction       => 'ti-alert-triangle',
            self::Damage            => 'ti-alert-circle',
            self::Lost              => 'ti-search',
            self::EndOfContract     => 'ti-calendar-x',
            self::PersonalReason    => 'ti-user',
        };
    }

    public static function values(): array
    {
        return array_map(fn(self $t) => $t->value, self::cases());
    }
}
