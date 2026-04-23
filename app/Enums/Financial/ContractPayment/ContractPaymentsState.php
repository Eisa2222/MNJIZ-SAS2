<?php

namespace App\Enums\Financial\ContractPayment;

enum ContractPaymentsState: string
{
    case NoPayments    = 'no_payments';     // لا توجد دفعات
    case FullyPaid     = 'fully_paid';      // جميع الدفعات مدفوعة
    case Scheduled     = 'scheduled';       // جميع الدفعات مجدولة
    case PartiallyPaid = 'partially_paid';  // بعضها مدفوع وبعضها مجدول
    case Overdue       = 'overdue';         // توجد دفعات متأخرة

    public function label(): string
    {
        return match ($this) {
            self::NoPayments    => 'بدون دفعات',
            self::FullyPaid     => 'مدفوع كلياً',
            self::Scheduled     => 'مجدول',
            self::PartiallyPaid => 'مدفوع جزئياً',
            self::Overdue       => 'متأخّر',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn(self $state) => ['id' => $state->value, 'name' => $state->label()],
            self::cases()
        );
    }

    public function color(): string
    {
        return match ($this) {
            self::NoPayments    => 'secondary', 
            self::FullyPaid     => 'success',   
            self::Scheduled     => 'info',     
            self::PartiallyPaid => 'primary',   
            self::Overdue       => 'danger',    
        };
    }
}
