<?php

namespace App\Enums\OrganizationCenter\Tasks\Task;

enum TaskRoutingAction: string
{
    case Assign = 'assign';
    case Return = 'return';

    public function label(): string
    {
        return match ($this) {
            self::Assign => 'إسناد',
            self::Return => 'إرجاع',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Assign => 'primary',
            self::Return => 'warning',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn(self $action) => ['id' => $action->value, 'name' => $action->label()],
            self::cases()
        );
    }

    public static function values(): array
    {
        return array_map(fn(self $action) => $action->value, self::cases());
    }
}
