<?php

namespace App\Enums\Hr\EditRequest;


enum EditRequestStatus: string
{
    case Pending                = 'pending';
    case Approved               = 'approved';
    case Rejected               = 'rejected';
    case PartiallyApproved      = 'partially_approved';



    public function label(): string
    {
        return match ($this) {
            self::Pending                   => 'بانتظار الموافقة',
            self::Approved                  => 'معتمدة',
            self::Rejected                  => 'مرفوضة',
            self::PartiallyApproved         => 'معتمد جزئيا',
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
            self::Pending               => 'primary',
            self::Approved              => 'success',
            self::Rejected              => 'danger',
            self::PartiallyApproved     => 'info',
        };
    }
}
