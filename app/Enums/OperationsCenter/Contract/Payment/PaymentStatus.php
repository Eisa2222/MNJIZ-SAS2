<?php

namespace App\Enums\OperationsCenter\Contract\Payment;

enum PaymentStatus: string
{
    case Scheduled = 'scheduled';
    case Paid      = 'paid';
    case Late      = 'late';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'مجدولة',
            self::Paid      => 'مدفوعة',
            self::Late      => 'متأخرة',
            self::Cancelled => 'ملغاة',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Scheduled => 'info',
            self::Paid      => 'success',
            self::Late      => 'warning',
            self::Cancelled => 'secondary',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn(self $s) => ['id' => $s->value, 'name' => $s->label()],
            self::cases()
        );
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function isScheduled(): bool
    {
        return $this === self::Scheduled;
    }

    public function isPaid(): bool
    {
        return $this === self::Paid;
    }

    public function isLate(): bool
    {
        return $this === self::Late;
    }

    public function isCancelled(): bool
    {
        return $this === self::Cancelled;
    }
    
}
