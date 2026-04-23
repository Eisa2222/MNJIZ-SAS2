<?php

namespace App\Enums\Hr\Custody\Item;

enum CustodyUseStatus: string
{
    case Available      = 'available';
    case InUse          = 'in_use';
    case Maintenance    = 'maintenance';
    case Stale          = 'stale';


    public function label(): string
    {
        return match ($this) {
            self::Available     => 'متاح',
            self::InUse         => 'قيد الاستخدام',
            self::Maintenance   => 'تحت الصيانة',
            self::Stale         => 'هالك',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Available     => 'success',
            self::InUse         => 'primary',
            self::Maintenance   => 'danger',
            self::Stale         => 'warning',
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
        return array_map(fn(self $t) => $t->value, self::cases());
    }
}
