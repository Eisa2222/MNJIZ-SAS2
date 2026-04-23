<?php

namespace App\Enums\OrganizationCenter\Tasks\Task;

enum TaskPriority: string
{
    case LOW    = 'low';
    case MEDIUM = 'medium';
    case HIGH   = 'high';

    public function label(): string
    {
        return match ($this) {
            self::LOW    => 'منخفضة',
            self::MEDIUM => 'متوسطة',
            self::HIGH   => 'مرتفعة',
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
            self::LOW    => 'success',
            self::MEDIUM => 'warning',
            self::HIGH   => 'danger',
        };
    }

    public static function values(): array
    {
        return array_map(fn(self $t) => $t->value, self::cases());
    }
}
