<?php

namespace App\Enums\Hr\Reward;

enum RewardStatus: string
{
    case Pending    = 'pending';
    case Approved   = 'approved';
    case Rejected   = 'rejected';

    case Executed   = 'executed';



    public function label(): string
    {
        return match ($this) {
            self::Pending  => 'بانتظار الموافقة',
            self::Approved => 'معتمدة',
            self::Rejected => 'مرفوضة',
            self::Rejected => 'مرفوضة',

            self::Executed => 'تم التنفيذ',
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

            self::Executed => 'primary',
        };
    }

    /*
    |============================================================================
    |============================================================================
    |                          Custom method
    |============================================================================
    |============================================================================
    */
    public function canEdit(): bool
    {
        return $this === self::Pending;
    }

    public function canDelete(): bool
    {
        return in_array($this, [
            self::Pending,
            self::Rejected
        ]);
    }
}
