<?php
namespace App\Enums\Qoyod\Products;

enum ExemptTaxReason: int
{
    case FINANCIAL_SERVICES        = 12; // خدمات مالية
    case LIFE_INSURANCE_SERVICES   = 13; // خدمات التأمين على الحياة
    case REAL_ESTATE_TRANSACTIONS  = 14; // المعاملات العقارية

    public function label(): string
    {
        return match($this) {
            self::FINANCIAL_SERVICES       => 'خدمات مالية',
            self::LIFE_INSURANCE_SERVICES  => 'خدمات التأمين على الحياة',
            self::REAL_ESTATE_TRANSACTIONS => 'المعاملات العقارية',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn(ExemptTaxReason $r) => ['id' => $r->value, 'name' => $r->label()],
            self::cases()
        );
    }
}
