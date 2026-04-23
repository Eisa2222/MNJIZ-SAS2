<?php

namespace App\Models\GeneralSetting\SystemSetting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class Settings extends Model
{
    use HasFactory;
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
            Cache::forget('qoyod_api_key');
            Cache::forget('qoyod_base_url');

            Cache::put('qoyod_api_key', $settings->qoyod_api_key, now()->addMinutes(30));
            Cache::put('qoyod_base_url', $settings->qoyod_base_url, now()->addMinutes(30));
        });
    }
}
