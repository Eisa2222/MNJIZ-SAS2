<?php

namespace Database\Seeders;

use App\Models\GeneralSetting\SystemSetting\Settings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Settings::updateOrCreate(
            ['id' => 1],
            [
                'office_name'                   => 'مُنجِز',
                'colors'                        => ["#54c8f3", "#7ad4f6", "#a0e0f9", "#3bb2dc", "#2a9cc5", "#1f86ad"],
                'sms_enabled'                   => 0,
                'whatsapp_enabled'              => 0,
                'archive_delete_duration'       => 30,
                'maintenance_mode'              => 0,
                'logo_text'                     => 'ETMAM-TEC',
                'biostation_api_url'            => 'https://api.biostation.com',
                'biostation_device_port'        => 80,
                'biostation_timezone'           => 'UTC',
                'biostation_sync_interval'      => 5,
                'manual_attendance_enabled'     => 0,
                'chatgpt_enabled'               => 0,
                // NEVER hardcode the Microsoft client secret — it ends up in git
                // history AND in the settings table. Read from env; the previous
                // literal was committed (now rotated in Azure AD portal).
                'microsoft_client_id'           => env('MICROSOFT_CLIENT_ID', ''),
                'microsoft_client_secret'       => env('MICROSOFT_CLIENT_SECRET', ''),
                'microsoft_redirect_uri'        => env('MICROSOFT_REDIRECT_URI', ''),
                'microsoft_tenant_id'           => env('MICROSOFT_TENANT_ID', ''),
                'main_email'                    => env('SETTINGS_MAIN_EMAIL', 'info@example.com'),


                'updated_at' => now(),
            ]
        );
    }
}
