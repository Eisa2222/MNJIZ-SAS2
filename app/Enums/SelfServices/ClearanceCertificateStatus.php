<?php

namespace App\Enums\SelfServices;

enum ClearanceCertificateStatus: string
{
    case PENDING  = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    /**
     * الحصول على التسمية النصية للحالة (Label).
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING  => 'قيد الانتظار',
            self::APPROVED => 'معتمد',
            self::REJECTED => 'مرفوض',
        };
    }

    /**
     * الحصول على اللون الممثل للحالة (للاستخدام في الواجهات).
     */
    public function color(): string
    {
        return match ($this) {
            self::PENDING  => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
        };
    }
}
