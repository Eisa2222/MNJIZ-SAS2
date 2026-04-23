<?php

namespace App\Enums\Hr\Reward;

enum RewardType: string
{
    case Commission = 'commission';
    case Grant      = 'grant';
    case Other      = 'other';



    public function label(): string
    {
        return match ($this) {
            self::Commission => 'عمولة',
            self::Grant      => 'منحة',
            self::Other      => 'أخرى',
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
