<?php

namespace App\Enums\OrganizationCenter\Tasks\Task;

enum TaskField: string
{
    case Offers            = 'offer';
    case Contracts         = 'contract';
    case Projects          = 'projects';
    case Lawsuits          = 'lawsuits';
    case Sessions          = 'sessions';
    case PowerOfAttorney   = 'power_of_attorney';
    case Marketing         = 'marketing';
    case Renewals          = 'renewals';
    case Sales             = 'sales';
    case Other             = 'other';

    case ClearanceCertificate          = 'clearance_certificate';
    case Advance                       = 'advance';
    case Reward                        = 'reward';
    case Deduction                     = 'deduction';
    case ContentManagement             = 'content';
    case Custody                       = 'custody';
    case Leave                         = 'leave';
    case WPS                           = 'wps';


    public function label(): string
    {
        return match ($this) {
            self::Offers          => 'العروض',
            self::Contracts       => 'العقود',
            self::Projects        => 'المشاريع',
            self::Lawsuits        => 'الدعاوى',
            self::Sessions        => 'الجلسات',
            self::PowerOfAttorney => 'الوكالات',
            self::Marketing       => 'التسويق',
            self::Renewals        => 'التجديدات',
            self::Sales           => 'المبيعات',

            self::ClearanceCertificate        => 'إخلاء طرف',
            self::Advance                     => 'سلفة',
            self::Reward                      => 'مكافأة',
            self::Deduction                   => 'خصم',
            self::ContentManagement           => 'محتوى',
            self::Custody                     => 'عهدة',

            self::Leave                       => 'الإجازات',
            self::WPS                         => 'مسير الرواتب',

            self::Other                       => 'أخرى',
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
            self::Offers          => 'info',
            self::Contracts       => 'success',
            self::Projects        => 'primary',
            self::Lawsuits        => 'danger',
            self::Sessions        => 'warning',
            self::PowerOfAttorney => 'dark',
            self::Marketing       => 'info',
            self::Renewals        => 'indigo',
            self::Sales           => 'success',

            self::ClearanceCertificate        => 'info',
            self::Advance                     => 'info',
            self::Reward                      => 'success',
            self::Deduction                   => 'danger',
            self::ContentManagement           => 'info',
            self::Custody                     => 'primary',
            self::Leave                       => 'info',
            self::WPS                         => 'info',

            self::Other                       => 'secondary',
        };
    }

    public static function values(): array
    {
        return array_map(fn(self $t) => $t->value, self::cases());
    }
}
