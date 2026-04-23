<?php

namespace App\Enums\OrganizationCenter\Tasks\Task;

enum TaskStatus: string
{
    case Pending            = 'pending';
    case InProgress         = 'in_progress';
    case Completed          = 'completed';
    case CancelCompletion   = 'cancel_completion';
    case Returned           = 'returned';

    public function label(): string
    {
        return match ($this) {
            self::Pending            => 'قيد الانتظار',
            self::InProgress         => 'قيد التنفيذ',
            self::Completed          => 'مكتملة',
            self::CancelCompletion   => 'تم إلغاء الإكمال',
            self::Returned           => 'تم إرجاع المهمة',
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
            self::Pending            => 'secondary',
            self::InProgress         => 'primary',
            self::Completed          => 'success',
            self::CancelCompletion   => 'danger',
            self::Returned           => 'danger',
        };
    }

    public static function values(): array
    {
        return array_map(fn(self $t) => $t->value, self::cases());
    }
}
