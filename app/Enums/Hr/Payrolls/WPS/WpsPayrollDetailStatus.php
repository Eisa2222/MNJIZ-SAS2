<?php

namespace App\Enums\Hr\Payrolls\WPS;



enum WpsPayrollDetailStatus: string
{
    case NotRequested       = 'not_requested';
    case Pending            = 'pending';
    case ReviewedApproved   = 'reviewed_approved';
    case ReviewedRejected   = 'reviewed_rejected';



    public function label(): string
    {
        return match ($this) {

            self::NotRequested          => 'لم يتم طلب المراجعة',
            self::Pending               =>   'في انتظار الموافقة',
            self::ReviewedApproved      => 'تمت المراجعة والتعديل',
            self::ReviewedRejected      =>  'تمت المراجعة الرفض',
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
            self::NotRequested          => 'secondary',
            self::Pending               => 'warning',
            self::ReviewedApproved      => 'success',
            self::ReviewedRejected      => 'danger',
        };
    }
}
