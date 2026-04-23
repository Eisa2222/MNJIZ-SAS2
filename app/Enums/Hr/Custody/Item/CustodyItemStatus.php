<?php

namespace App\Enums\Hr\Custody\Item;

enum CustodyItemStatus: string
{
    case New        = 'new';
    case Used       = 'used';

    public function label(): string
    {
        return match ($this) {
            self::New   => 'جديد',
            self::Used  => 'مستعمل',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New   => 'primary',
            self::Used  => 'secondary',
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
        return array_map(fn(self $t) => $t->value, self::cases());
    }
}
