<?php

namespace App\Enums\OperationsCenter\Contract\Payment;

enum PaymentMethod: string
{
    case Cash         = 'cash';
    case BankTransfer = 'bank_transfer';
    case Cheque       = 'cheque';
    case CreditCard   = 'credit_card';

    public function label(): string
    {
        return match ($this) {
            self::Cash         => 'نقدي',
            self::BankTransfer => 'تحويل بنكي',
            self::Cheque       => 'شيك',
            self::CreditCard   => 'بطاقة ائتمان',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn(self $m) => ['id' => $m->value, 'name' => $m->label()],
            self::cases()
        );
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
