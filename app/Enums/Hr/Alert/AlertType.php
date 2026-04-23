<?php
// app/Enums/Hr/Alert/AlertType.php

namespace App\Enums\Hr\Alert;

enum AlertType: string
{
    case WorkLicenseExpiry = 'work_license_expiry';
    case ContractExpiry    = 'contract_expiry';
    case TrainingExpiry    = 'training_expiry';

    public function label(): string
    {
        return match ($this) {
            self::WorkLicenseExpiry => 'انتهاء رخصة العمل',
            self::ContractExpiry    => 'انتهاء العقد',
            self::TrainingExpiry    => 'انتهاء فترة التدريب',
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
            self::WorkLicenseExpiry => 'danger',
            self::ContractExpiry    => 'primary',
            self::TrainingExpiry    => 'secondary',
        };
    }


    public static function fromFieldName(string $fieldName): ?self
    {
        return match ($fieldName) {
            'contract_end_date'     => self::ContractExpiry,
            'training_end_date'     => self::TrainingExpiry,
            'work_license_end_date' => self::WorkLicenseExpiry,
            default => null,
        };
    }

    public function getFieldName(): string
    {
        return match ($this) {
            self::ContractExpiry    => 'contract_end_date',
            self::TrainingExpiry    => 'training_end_date',
            self::WorkLicenseExpiry => 'work_license_end_date',
        };
    }
}
