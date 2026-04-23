<?php

namespace App\Enums\LegalAffair\Lawsuit;

enum LawsuitStatus: string
{
    case Active   = 'active';
    case Inactive = 'inactive';
    case Pending  = 'pending';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Active   => 'نشط',
            self::Inactive => 'مغلقة',
            self::Pending  => 'في الانتظار',
            self::Rejected => 'مرفوض',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active   => 'success',
            self::Inactive => 'secondary',
            self::Pending  => 'warning',
            self::Rejected => 'danger',
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
