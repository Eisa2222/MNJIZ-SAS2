<?php

namespace App\Enums\Hr\EditRequest;


enum FieldName: string
{
    case Nickname                       = 'nickname';
    case BirthDate                      = 'birth_date';
    case QualificationDegree            = 'qualification_degree';

    case PersonalEmail                  = 'personal_email';
    case Mobile                         = 'mobile';

    case address                        = 'address';
    case resume                         = 'resume';
    case qualification_certificate      = 'qualification_certificate';
    case contract_attachment            = 'contract_attachment';
    case id_attachment                  = 'id_attachment';
    case bank_account_attachment        = 'bank_account_attachment';
    case national_address_attachment    = 'national_address_attachment';
    case signature                      = 'signature';




    public function label(): string
    {
        return match ($this) {
            self::Nickname                      => 'اللقب',
            self::BirthDate                     => 'تاريخ الميلاد',
            self::QualificationDegree           => 'درجة التأهيل',
            self::PersonalEmail                 => 'البريد الإلكتروني الشخصي',
            self::Mobile                        => 'رقم الجوال',
            self::address                       => 'العنوان',
            self::resume                        => 'السيرة الذاتية',
            self::qualification_certificate     => 'شهادة التأهيل',
            self::contract_attachment           => 'مرفق العقد',
            self::id_attachment                 => 'مرفق الهوية',
            self::bank_account_attachment       => 'مرفق الحساب البنكي',
            self::national_address_attachment   => 'مرفق العنوان الوطني',
            self::signature                     => 'التوقيع',
        };
    }


    public static function options(): array
    {
        return array_map(
            fn(self $t) => ['id' => $t->value, 'name' => $t->label()],
            self::cases()
        );
    }
}
