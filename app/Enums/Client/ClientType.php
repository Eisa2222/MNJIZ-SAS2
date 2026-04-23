<?php

namespace App\Enums\Client;

enum ClientType: string
{
    case Individual  = 'individual';
    case LegalEntity = 'legal_entity';


    public function label(): string
    {
        return match ($this) {
            self::Individual  => 'فرد',
            self::LegalEntity => 'شخصية اعتبارية',
        };
    }


    public static function options(): array
    {
        return array_map(
            fn(self $type) => ['id' => $type->value, 'name' => $type->label()],
            self::cases()
        );
    }

    // تعيد جميع القيم الممكنة للنوع
    public static function values(): array
    {
        return array_map(fn(self $t) => $t->value, self::cases());
    }
}
