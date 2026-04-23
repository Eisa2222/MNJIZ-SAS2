<?php

namespace App\Enums\Qoyod\Accounts;

enum AccountType: string
{
    case Asset      = 'Asset';
    case Liability  = 'Liability';
    case Expense    = 'Expense';
    case Equity     = 'Equity';
    case Revenue    = 'Revenue';

    public function label(): string
    {
        return match ($this) {
            self::Asset         => 'الاصول',
            self::Liability     => 'الالتزامات',
            self::Expense       => 'المصاريف',
            self::Equity        => 'حقوق الملاك',
            self::Revenue       => 'الايرادات',
        };
    }


    public static function options(): array
    {
        return array_map(
            fn(AccountType $type) => [
                'id' => $type->value,
                'name' => $type->label(),
            ],
            self::cases()
        );
    }

    public static function values(): array
    {
        return array_map(fn(self $c) => $c->value, self::cases());
    }
}
