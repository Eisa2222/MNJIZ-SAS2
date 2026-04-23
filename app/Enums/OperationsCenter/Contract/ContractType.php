<?php

namespace App\Enums\OperationsCenter\Contract;

enum ContractType: string
{
    case Main           = 'main';
    case Supplementary  = 'supplementary';



    public function label(): string
    {
        return match ($this) {
            self::Main          => 'رئيسي',
            self::Supplementary => 'ملحق',
        };
    }


    public static function options(): array
    {
        return array_map(
            fn(self $status) => ['id' => $status->value, 'name' => $status->label()],
            self::cases()
        );
    }


    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
