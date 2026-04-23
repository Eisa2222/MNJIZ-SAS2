<?php

namespace App\Enums\LegalAffair\PowerOfAttorney;

enum PowerOfAttorneyStatus: string
{
    case Active   = 'active';
    case Expired  = 'expired';
    case Revoked  = 'revoked';

    public function label(): string
    {
        return match ($this) {
            self::Active   => 'نشط',
            self::Expired  => 'منتهي الصلاحية',
            self::Revoked  => 'ملغي',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active   => 'success',
            self::Expired  => 'warning',
            self::Revoked  => 'danger',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn(self $t) => ['id' => $t->value, 'name' => $t->label()],
            self::cases()
        );
    }

    public static function values(): array
    {
        return array_map(fn(self $s) => $s->value, self::cases());
    }
}
