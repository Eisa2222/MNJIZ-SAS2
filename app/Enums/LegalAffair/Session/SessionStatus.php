<?php

namespace App\Enums\LegalAffair\Session;


enum SessionStatus: string
{
    case Active                 = 'active';
    case Inactive               = 'inactive';
    case PendingSessionControl  = 'pending_session_control';

    public function label(): string
    {
        return match ($this) {
            self::Active   => 'نشطة',
            self::Inactive => 'مغلقة',
            self::PendingSessionControl => 'بانتظار ضبط الجلسة',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active   => 'success',
            self::Inactive => 'secondary',
            self::PendingSessionControl => 'info',
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


    public function canEditOrDelete(): bool
    {
        return in_array($this, [
            self::Active,
            self::PendingSessionControl,
        ], true);
    }
}
