<?php

namespace App\Models\GeneralSetting\SystemSetting;

use App\Tenancy\Concerns\BelongsToTenant;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class Settings extends Model
{
    use HasFactory, BelongsToTenant;
    protected $fillable = [
        'image',
        // 'site_title',
        'office_name',
        'colors',

        // microsoft
        'microsoft_client_id',
        'microsoft_client_secret',
        'microsoft_redirect_uri',
        'microsoft_tenant_id',
        'main_email',
        // microsoft

        // حقول الرسائل النصية
        'sms_provider',
        'sms_api_key',
        'sms_api_secret',
        'sms_api_endpoint',
        'sms_sender_id',
        'sms_from_number',
        'sms_callback_url',
        'sms_enabled',


        // حقول الرسائل النصية
        'archive_delete_duration',

        'template_image',
        'signature',
        'maintenance_mode',
        'logo_text',

        // biostation device
        'biostation_api_key',
        'biostation_api_url',
        'biostation_device_ip',
        'biostation_device_port',
        'biostation_timezone',
        'biostation_device_name',
        'biostation_sync_interval',
        'biostation_last_sync',
        'work_start_time',
        'work_end_time',
        'manual_attendance_enabled',
        'company_latitude',
        'company_longitude',
        'daily_working_hours',
        'payroll_disbursement_day',
        'weekly_days_off',
        'support_emails',
        'attendance_start_time',
        'attendance_end_time',
        'departure_start_time',
        'attendance_system_locked',
        'insurance_deduction',
        'insurance_percentage', // نسبة التامينات

        'qoyod_api_key',

    ];

    protected $casts = [
        'colors'                        => 'array',
        'support_emails'                => 'array',
        'weekly_days_off'               => 'array',
        'attendance_system_locked'      => 'boolean',
        'insurance_deduction'           => 'boolean',
        'qoyod_api_key'                 => 'encrypted',
    ];

    // Mutator لتشفير client_secret عند التخزين
    public function setMicrosoftClientSecretAttribute($value)
    {
        if ($value) {
            $this->attributes['microsoft_client_secret'] = Crypt::encryptString($value);
        }
    }

    // Accessor لفك تشفير client_secret عند الجلب
    public function getMicrosoftClientSecretAttribute($value)
    {
        if ($value == null) {
            return;
        }
        return Crypt::decryptString($value);
    }


    protected static function booted()
    {
        static::saved(function ($settings) {
            // Per-tenant cache keys — Module 5 isolation. Previously a single
            // global 'qoyod_api_key' entry leaked tenant A's key to tenant B.
            $tenantId = $settings->tenant_id ?? TenantContext::currentId();
            $apiKeyCache  = self::qoyodCacheKey('api_key',  $tenantId);
            $baseUrlCache = self::qoyodCacheKey('base_url', $tenantId);

            Cache::forget($apiKeyCache);
            Cache::forget($baseUrlCache);

            Cache::put($apiKeyCache,  $settings->qoyod_api_key,  now()->addMinutes(30));
            Cache::put($baseUrlCache, $settings->qoyod_base_url, now()->addMinutes(30));
        });
    }

    /**
     * Tenant-scoped cache key for legacy singleton integration credentials.
     * The old code wrote under the literal 'qoyod_api_key' key; under
     * multi-tenancy every tenant now owns its own slot.
     */
    public static function qoyodCacheKey(string $suffix, ?int $tenantId = null): string
    {
        $tenantId ??= TenantContext::currentId() ?? 0;
        return "tenant_{$tenantId}_qoyod_{$suffix}";
    }

    /**
     * Return THE settings row for the current tenant, or an empty unsaved
     * Settings instance if none exists yet.
     *
     * Replaces the legacy singleton access pattern:
     *   Settings::find(1)
     *   Settings::first()   // fine post-trait, but ambiguous
     *
     * Behavior:
     *   - Scoped via BelongsToTenant → only the current tenant's row.
     *   - If no row exists, returns a NEW unsaved instance (not persisted)
     *     so caller code like `$settings->office_name` stays null-safe —
     *     mirrors the legacy `Settings::first()` returning null behavior,
     *     but without crashing on `$settings->x`.
     *
     * Use `$settings->exists` to check whether there is actually a row.
     *
     * Super-admin / central context (no tenant resolved) returns the
     * default tenant's row to keep artisan boot paths functional.
     */
    public static function current(): self
    {
        $tenantId = TenantContext::currentId();

        if ($tenantId === null) {
            $row = static::withoutTenancy()->orderBy('id')->first();
            return $row ?? new self();
        }

        return static::firstOrNew(['tenant_id' => $tenantId]);
    }
}
