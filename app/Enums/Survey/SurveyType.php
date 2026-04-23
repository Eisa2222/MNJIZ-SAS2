<?php

namespace App\Enums\Survey;


enum SurveyType: string
{
    case Session        = 'session';
    case Lawsuit        = 'lawsuit';
    case Contract       = 'contract';


    public function label(): string
    {
        return match ($this) {
            self::Session              => 'الجلسات',
            self::Lawsuit              => 'الدعاوى',
            self::Contract             => 'التعاقد',
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
