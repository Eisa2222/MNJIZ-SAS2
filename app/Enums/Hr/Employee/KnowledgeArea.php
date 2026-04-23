<?php

namespace App\Enums\Hr\Employee;

enum KnowledgeArea: string
{
    case InheritanceAndWills         = 'inheritance_and_wills';         // التركات والوصايا
    case IntellectualProperty        = 'intellectual_property';         // الملكية الفكرية
    case RealEstate                  = 'real_estate';                   // العقارات
    case Endowments                  = 'endowments';                    // الاوقاف
    case GovConsultingAndLegislation = 'gov_consulting_and_legislation'; // الاستشارات الحكومية والتشريعات والتخصيص

    public function label(): string
    {
        return match ($this) {
            self::InheritanceAndWills         => 'التركات والوصايا',
            self::IntellectualProperty        => 'الملكية الفكرية',
            self::RealEstate                  => 'العقارات',
            self::Endowments                  => 'الاوقاف',
            self::GovConsultingAndLegislation => 'الاستشارات الحكومية والتشريعات والتخصيص',
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
