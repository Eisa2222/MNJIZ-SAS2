<?php

namespace App\Enums\Survey\SurveyResponse;



enum SurveyResponseStatus: string
{
    case Pending     = 'pending';
    case Completed   = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Pending    => 'قيد الانتظار',
            self::Completed  => 'مكتمل',
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
            self::Pending    => 'primary',
            self::Completed  => 'success',
        };
    }
}
