<?php

namespace App\Enums\OperationsCenter\Contract\Payment;

enum CalculationType: string
{
    case Percentage = 'percentage';
    case Fixed      = 'fixed';

    public function label(): string
    {
        return match ($this) {
            self::Percentage => 'نسبة',
            self::Fixed      => 'مبلغ',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn(self $type) => ['id' => $type->value, 'name' => $type->label()],
            self::cases()
        );
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }



    public function isPercentage(): bool
    {
        return $this === self::Percentage;
    }
}
