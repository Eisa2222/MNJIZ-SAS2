<?php

namespace App\Enums\Hr\ViolationsPenalties;

enum ViolationStatus: string
{
    case Pending        = 'pending';
    case Approved       = 'approved';
    case Rejected       = 'rejected';

    case UnderAppeal        = 'under_appeal';
    case AppealSubmitted    = 'appeal_submitted';

    case Cancelled      = 'cancelled';
    case Executed       = 'executed';

    public function label(): string
    {
        return match ($this) {
            self::Pending       => 'بانتظار الموافقة',
            self::Approved      => 'معتمدة',
            self::Rejected      => 'مرفوضة',

            self::UnderAppeal       => 'قيد التظلم',
            self::AppealSubmitted   => 'تم تقديم التظلم',


            self::Cancelled      => 'ملغي',
            self::Executed       => 'تم التنفيذ',
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
            self::Pending           => 'primary',
            self::Approved          => 'success',
            self::Rejected          => 'danger',

            self::UnderAppeal       => 'info',
            self::AppealSubmitted   => 'warning',

            self::Cancelled         => 'secondary',
            self::Executed          => 'primary',
        };
    }


    public function canEditOrDelete(): bool
    {
        return in_array($this, [
            self::Pending,
            self::UnderAppeal,
        ], true);
    }


    public function canApplyPenalty(): bool
    {
        return in_array($this, [
            self::Pending,
            self::UnderAppeal,
            self::AppealSubmitted, 
        ], true);
    }
}
