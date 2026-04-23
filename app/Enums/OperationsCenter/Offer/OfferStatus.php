<?php

namespace App\Enums\OperationsCenter\Offer;

enum OfferStatus: string
{
    case UNDER_STUDY      = 'under_study';
    case PENDING          = 'pending';
    case APPROVED         = 'approved';
    case REJECTED         = 'rejected';
    case WAITING_CUSTOMER = 'waiting_customer';
    case CONTRACTED       = 'Contracted';
    case EXPIRED          = 'expired';


    public function label(): string
    {
        return match ($this) {
            self::UNDER_STUDY      => 'تحت الدراسة',
            self::PENDING          => 'بانتظار الإعتماد',
            self::APPROVED         => 'معتمدة',
            self::REJECTED         => 'مرفوضة',
            self::WAITING_CUSTOMER => 'بانتظار العميل',
            self::CONTRACTED       => 'تم التعاقد',
            self::EXPIRED          => 'منتهي الصلاحية',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::UNDER_STUDY      => 'secondary',
            self::PENDING          => 'warning',
            self::APPROVED         => 'success',
            self::REJECTED         => 'danger',
            self::WAITING_CUSTOMER => 'info',
            self::CONTRACTED       => 'primary',
            self::EXPIRED          => 'dark',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn(self $t) => ['id' => $t->value, 'name' => $t->label()],
            self::cases()
        );
    }

    public static function values(): array
    {
        return array_map(fn(self $s) => $s->value, self::cases());
    }
}
