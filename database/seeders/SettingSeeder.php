<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('settings')->insert([
            'colors' => json_encode(['#d5a047', '#e3b85e', '#f1cc75', '#bf8f3f', '#a77c35', '#8f6a2b']), // تحويل المصفوفة إلى JSON
            'archive_delete_duration'=>30,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
