<?php

namespace App\Enums\Survey;


enum SurveyStatus: string
{
    case Active    = 'active';
    case InActive   = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Active    => 'نشط',
            self::InActive  => 'غير نشط',
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
            self::Active    => 'success',
            self::InActive  => 'danger',
        };
    }
}
