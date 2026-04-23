<?php

namespace App\Enums\Marketing\ContentManagement;

enum PublishType: string
{
    case OneTime   = 'one_time';
    case Recurring = 'recurring';

    public function label(): string
    {
        return match ($this) {
            self::OneTime   => 'نشر مرة واحدة',
            self::Recurring => 'نشر متكرر',
        };
    }

    public static function values(): array
    {
        return array_map(fn(self $type) => $type->value, self::cases());
    }
}