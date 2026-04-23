<?php

namespace App\Enums\Hr\Payrolls\WPS;

enum WpsStatus: string
{
    case GeneratedAutomatically = 'generated_automatically';
    case GeneratedManually      = 'generated_manually';
    case Modified               = 'modified';

    case Pending                = 'pending';
    case Approved               = 'approved';
    case Rejected               = 'rejected';

    case ExportedToBank         = 'exported_to_bank';




    public function label(): string
    {
        return match ($this) {

            self::GeneratedAutomatically    => 'تم التوليد التلقائي',
            self::GeneratedManually         =>  'تم التوليد اليدوي',
            self::Modified                  => 'تم التعديل على المسير',

            self::Pending                   => 'تم إرسال المسير للإدارة',
            self::Approved                  => 'تمت الموافقة من الإدارة',
            self::Rejected                  =>  'تم رفض المسير',

            self::ExportedToBank            => 'تم تصدير المسير للبنك',
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
            self::GeneratedAutomatically    => 'secondary',
            self::GeneratedManually         => 'dark',
            self::Modified                  => 'info',

            self::Pending                   => 'warning',
            self::Approved                  => 'success',
            self::Rejected                  => 'danger',

            self::ExportedToBank            => 'primary',
        };
    }
}
