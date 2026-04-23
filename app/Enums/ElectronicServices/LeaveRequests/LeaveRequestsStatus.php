<?php

namespace App\Enums\ElectronicServices\LeaveRequests;

enum LeaveRequestsStatus: string
{
    case Pending    = 'pending';
    case Approved   = 'approved';
    case Rejected   = 'rejected';


    public function label(): string
    {
        return match ($this) {
            self::Pending  => 'بانتظار الموافقة',
            self::Approved => 'معتمدة',
            self::Rejected => 'مرفوضة',
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
            self::Pending  => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
        };
    }

    /*
    |============================================================================
    |============================================================================
    |                          Custom method
    |============================================================================
    |============================================================================
    */
    public function canEditOrDelete(): bool
    {
        return $this === self::Pending;
    }
}
