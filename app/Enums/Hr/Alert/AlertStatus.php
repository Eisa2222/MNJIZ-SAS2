<?php

namespace App\Enums\Hr\Alert;


enum AlertStatus: string
{
    case New         = 'new';
    case Resolved    = 'resolved';

    public function label(): string
    {
        return match ($this) {
            self::New           => 'جديد',
            self::Resolved      => 'تم  التجديد',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn(self $t) => ['id' => $t->value, 'name' => $t->label()],
            self::cases()
        );
    }

    public function color(): string
    {
        return match ($this) {
            self::New           => 'primary',
            self::Resolved      => 'success',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::New           => 'fas fa-exclamation-triangle',  // أيقونة تحذير للجديد
            self::Resolved      => 'fas fa-check-circle',          // أيقونة صح للمعالج
        };
    }
}
