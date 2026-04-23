<?php

namespace App\Enums\OperationsCenter\Contract\Payment;

enum PaymentBatchType: string
{
    case Advance     = 'advance';
    case Deferred    = 'deferred';
    case Final       = 'final';


    public function label(): string
    {
        return match ($this) {
            self::Advance       => 'دفعة مقدمة',
            self::Deferred      => 'دفعة مؤجلة',
            self::Final         => 'دفعة نهائية',
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
}
