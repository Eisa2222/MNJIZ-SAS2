<?php

namespace App\Enums\Marketing\ContentManagement\SocialPublication;

enum SocialPublicationStatus: string
{

    case Pending    = 'pending';
    case Success    = 'success';
    case Failed     = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'في انتظار النشر ',
            self::Success   => 'تم النشر',
            self::Failed    => 'فشل النشر',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending   => 'secondary',
            self::Success   => 'success',
            self::Failed    => 'danger',
        };
    }

    public static function values(): array
    {
        return array_map(fn(self $s) => $s->value, self::cases());
    }
}
