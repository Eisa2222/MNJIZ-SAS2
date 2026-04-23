<?php

namespace App\Enums\Marketing\ContentManagement;

enum MediaType: string
{
    case Image      = 'image';        // صورة فقط
    case Video      = 'video';        // فيديو فقط
    case Text       = 'text';         // نص فقط
    case ImageText  = 'image_text';   // صورة + نص
    case VideoText  = 'video_text';   // فيديو + نص

    public function label(): string
    {
        return match ($this) {
            self::Image     => 'صورة',
            self::Video     => 'فيديو',
            self::Text      => 'نص',
            self::ImageText => 'صورة + نص',
            self::VideoText => 'فيديو + نص',
        };
    }

    public static function values(): array
    {
        return array_map(fn(self $m) => $m->value, self::cases());
    }
}
