<?php

namespace App\Enums\Marketing\ContentManagement;

enum ContentStatus: string
{
    case Pending    = 'pending';
    case Approved   = 'approved';
    case Rejected   = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'بانتظار الموافقة',
            self::Approved  => 'معتمدة',
            self::Rejected  => 'مرفوضة',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending   => 'warning',
            self::Approved  => 'success',
            self::Rejected  => 'danger',
        };
    }


    public static function values(): array
    {
        return array_map(fn(self $s) => $s->value, self::cases());
    }
}
