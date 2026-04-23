<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSocial extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $platforms = [
            ['name' => 'linkedin',  'icon' => 'ti ti-brand-linkedin'],
            ['name' => 'x',         'icon' => 'ti ti-brand-x'],
            ['name' => 'facebook',  'icon' => 'ti ti-brand-facebook'],
            ['name' => 'instagram', 'icon' => 'ti ti-brand-instagram'],
            ['name' => 'youtube',   'icon' => 'ti ti-brand-youtube'],
            ['name' => 'whatsapp',  'icon' => 'ti ti-brand-whatsapp'],
            ['name' => 'telegram',  'icon' => 'ti ti-brand-telegram'],
            ['name' => 'tiktok',    'icon' => 'ti ti-brand-tiktok'],
            ['name' => 'snapchat',  'icon' => 'ti ti-brand-snapchat'],
            ['name' => 'pinterest', 'icon' => 'ti ti-brand-pinterest'],
        ];

        foreach ($platforms as $platform) {
            DB::table('settings_socials')->insert([
                'name'       => $platform['name'],
                'icon'       => $platform['icon'],
                'is_active'  => false,
                'user_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
