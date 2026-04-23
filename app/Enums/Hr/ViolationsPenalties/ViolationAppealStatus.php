<?php

namespace App\Enums\Hr\ViolationsPenalties;

enum ViolationAppealStatus: string
{
    case Pending        = 'pending';
    case Approved       = 'approved';
    case Rejected       = 'rejected';


    public function label(): string
    {
        return match ($this) {
            self::Pending       => 'تحت المراجعة',
            self::Approved      => 'تظلم مقبول',
            self::Rejected      => 'تظلم مرفوض',

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
            self::Pending       => 'primary',
            self::Approved      => 'success',
            self::Rejected      => 'danger',
        };
    }

}
