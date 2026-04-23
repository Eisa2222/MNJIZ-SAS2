<?php

namespace App\Enums\Marketing\ContentManagement;

enum RecurringType: string
{
    case Daily   = 'daily';
    case Weekly  = 'weekly';
    case Monthly = 'monthly';

    public function label(): string
    {
        return match ($this) {
            self::Daily   => 'يومي',
            self::Weekly  => 'أسبوعي',
            self::Monthly => 'شهري',
        };
    }

    public static function values(): array
    {
        return array_map(fn(self $type) => $type->value, self::cases());
    }
}
