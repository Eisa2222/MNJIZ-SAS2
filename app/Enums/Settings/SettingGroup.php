<?php

declare(strict_types=1);

namespace App\Enums\Settings;

/**
 * Canonical grouping for settings in both central_settings and tenant_settings.
 * New groups must be added here so the admin UI and caching keys stay in sync.
 */
enum SettingGroup: string
{
    // Central (platform-wide)
    case Platform = 'platform';
    case Billing  = 'billing';
    case Email    = 'email';
    case Features = 'features';

    // Tenant (per firm)
    case General       = 'general';
    case Microsoft     = 'microsoft';
    case Qoyod         = 'qoyod';
    case Sms           = 'sms';
    case BioStation    = 'biostation';
    case WorkHours     = 'work_hours';
    case Payroll       = 'payroll';
    case Notifications = 'notifications';
    case LegalAi       = 'legal_ai';
    case Branding      = 'branding';

    public function label(): string
    {
        return match ($this) {
            self::Platform      => 'Platform',
            self::Billing       => 'Billing',
            self::Email         => 'Email',
            self::Features      => 'Features',
            self::General       => 'General',
            self::Microsoft     => 'Microsoft 365',
            self::Qoyod         => 'Qoyod (Accounting)',
            self::Sms           => 'SMS',
            self::BioStation    => 'BioStation (Biometric)',
            self::WorkHours     => 'Work Hours',
            self::Payroll       => 'Payroll',
            self::Notifications => 'Notifications',
            self::LegalAi       => 'Legal AI',
            self::Branding      => 'Branding',
        };
    }

    public static function centralOnly(): array
    {
        return [self::Platform, self::Billing, self::Email, self::Features];
    }

    public static function tenantOnly(): array
    {
        return [
            self::General, self::Microsoft, self::Qoyod, self::Sms, self::BioStation,
            self::WorkHours, self::Payroll, self::Notifications, self::LegalAi, self::Branding,
        ];
    }
}
