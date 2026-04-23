<?php

namespace App\Enums\ElectronicServices\Custody\Requests;

enum CustodyRequestType: string
{
    case Assign = 'assign';
    case Return = 'return';


    public function label(): string
    {
        return match ($this) {
            self::Assign => 'طلب عهدة جديدة',
            self::Return => 'طلب إرجاع عهدة',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn(self $t) => ['id' => $t->value, 'name' => $t->label()],
            self::cases()
        );
    }

    public function color(): string
    {
        return match ($this) {
            self::Assign => 'success', // أخضر
            self::Return => 'danger',  // أحمر
        };
    }

    public function canChange(): bool
    {
        return true;
    }
}
