<?php

namespace App\Enums\OperationsCenter\Contract;

enum ContractStatus: string
{
    case PENDING  = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    case ACTIVE   = 'active';   // ساري
    case EXPIRED  = 'expired';  // منتهي
    case CANCELED = 'canceled'; // ملغي
    case DRAFT    = 'draft';    // مسودة



    public function label(): string
    {
        return match ($this) {
            self::PENDING  => 'قيد الاعتماد',
            self::APPROVED => 'معتمدة',
            self::REJECTED => 'مرفوضة',
            self::ACTIVE   => 'ساري',
            self::EXPIRED  => 'منتهي',
            self::CANCELED => 'ملغي',
            self::DRAFT    => 'مسودة',
        };
    }


    public function color(): string
    {
        return match ($this) {
            self::PENDING  => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
            self::ACTIVE   => 'success',
            self::EXPIRED  => 'dark',
            self::CANCELED => 'secondary',
            self::DRAFT    => 'light',
        };
    }


    public static function options(): array
    {
        return array_map(
            fn(self $status) => ['id' => $status->value, 'name' => $status->label()],
            self::cases()
        );
    }


    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
