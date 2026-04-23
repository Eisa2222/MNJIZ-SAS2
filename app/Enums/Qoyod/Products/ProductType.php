<?php

namespace App\Enums\Qoyod\Products;

enum ProductType: string 
{
    case Product      = 'Product';
    case Service      = 'Service';
    case Expense      = 'Expense';
    case RawMaterial  = 'RawMaterial';
    case Recipe       = 'Recipe';

    public function label(): string
    {
        return match ($this) {
            self::Product     => 'منتج',
            self::Service     => 'خدمة',
            self::Expense     => 'مصروف',
            self::RawMaterial => 'مواد خام',
            self::Recipe      => 'وصفة',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Product     => 'ti ti-package',
            self::Service     => 'ti ti-briefcase',
            self::Expense     => 'ti ti-receipt',
            self::RawMaterial => 'ti ti-building-warehouse',
            self::Recipe      => 'ti ti-chef-hat',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn(ProductType $type) => [
                'id' => $type->value, 
                'name' => $type->label(),
                'icon' => $type->icon()
            ],
            self::cases()
        );
    }

    public static function getSelectableTypes(): array
    {
        return [
            self::Service,
            self::Expense,
            // self::Product,
            // self::RawMaterial,
            // self::Recipe,
        ];
    }
}