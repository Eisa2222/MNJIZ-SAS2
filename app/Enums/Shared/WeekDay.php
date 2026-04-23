<?php

namespace App\Enums\Shared;

enum WeekDay: int
{
    case Monday    = 1;
    case Tuesday   = 2;
    case Wednesday = 3;
    case Thursday  = 4;
    case Friday    = 5;
    case Saturday  = 6;
    case Sunday    = 7;

    public function label(): string
    {
        return match ($this) {
            self::Sunday    => 'الأحد',
            self::Monday    => 'الإثنين',
            self::Tuesday   => 'الثلاثاء',
            self::Wednesday => 'الأربعاء',
            self::Thursday  => 'الخميس',
            self::Friday    => 'الجمعة',
            self::Saturday  => 'السبت',
        };
    }

    public static function values(): array
    {
        return array_map(fn(self $day) => $day->value, self::cases());
    }
}
