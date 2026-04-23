<?php

namespace App\Enums\LegalAffair\Opponent;



enum OpponentType: string
{
    case Individual    = 'individual';
    case Company       = 'company';


    public function label(): string
    {
        return match ($this) {
            self::Individual      => 'فرد',
            self::Company          => 'شخصية اعتبارية',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Individual        => 'info',
            self::Company           => 'primary',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn(self $t) => ['id' => $t->value, 'name' => $t->label()],
            self::cases()
        );
    }

    public static function values(): array
    {
        return array_map(fn(self $s) => $s->value, self::cases());
    }
}
