<?php

namespace App\Enums\ElectronicServices\Custody\Requests;

enum CustodyRequestStatus: string
{
    case Pending    = 'pending';
    case Approved   = 'approved';
    case Rejected   = 'rejected';
    case Returned   = 'returned';

    public function label(): string
    {
        return match ($this) {
            self::Pending  => 'بانتظار الموافقة',
            self::Approved => 'معتمدة',
            self::Rejected => 'مرفوضة',
            self::Returned => 'تم إرجاع العهدة',
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
            self::Pending  => 'primary',
            self::Approved => 'success',
            self::Rejected => 'danger',
            self::Returned => 'secondary',
        };
    }

    public function canEditOrDelete(): bool
    {
        return $this === self::Pending;
    }
}
