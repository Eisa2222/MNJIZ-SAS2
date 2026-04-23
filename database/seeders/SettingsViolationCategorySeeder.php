<?php

namespace Database\Seeders;

use App\Models\general_setting\SettingsViolationCategory as GeneralSettingSettingsViolationCategory;
use Illuminate\Database\Seeder;
use App\Models\GeneralSetting\SystemSetting\SettingsViolationCategory;
use Illuminate\Support\Facades\DB;

class SettingsViolationCategorySeeder extends Seeder
{
    /**
     * تشغيل Seeder.
     */
    public function run(): void
    {
        // تعطيل قيود المفتاح الخارجي لتجنب المشاكل أثناء الإدخال
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // حذف البيانات القديمة
        GeneralSettingSettingsViolationCategory::truncate();

        // إعادة تمكين قيود المفتاح الخارجي
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // إدخال التصنيفات
        $categories = [
            ['name' => 'مخالفات تتعلق بمواعيد العمل', 'user_id' => 1, 'status' => 'active'],
            ['name' => 'مخالفات تتعلق بتنظيم العمل', 'user_id' => 1, 'status' => 'active'],
            ['name' => 'مخالفات تتعلق بسلوك العامل', 'user_id' => 1, 'status' => 'active'],
        ];

        foreach ($categories as $category) {
            GeneralSettingSettingsViolationCategory::create($category);
        }
    }
}
