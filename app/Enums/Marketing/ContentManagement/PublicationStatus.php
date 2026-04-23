<?php

namespace App\Enums\Marketing\ContentManagement;

enum PublicationStatus: string
{
    case Draft     = 'draft';
    case Scheduled = 'scheduled';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Draft     => 'مسودة',
            self::Scheduled => 'مجدوَلة',
            self::Published => 'منشورة',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft     => 'secondary',
            self::Scheduled => 'warning',
            self::Published => 'success',
        };
    }

    public static function values(): array
    {
        return array_map(fn(self $s) => $s->value, self::cases());
    }
}
