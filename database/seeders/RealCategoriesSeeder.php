<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\general_setting\SettingsCategories;
use App\Models\general_setting\SettingsSubcategories;
use App\Models\general_setting\SettingsLawsuitsType;

class RealCategoriesSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            'القضايا المدنية' => [
                'نزاعات الملكية' => ['دعوى إثبات ملكية', 'دعوى إزالة تعدي', 'دعوى إخلاء عقار'],
                'التعويضات' => ['دعوى تعويض عن أضرار مادية', 'دعوى تعويض عن أضرار نفسية'],
                'الإيجارات' => ['دعوى إخلاء مستأجر', 'دعوى زيادة إيجار'],
            ],
            'القضايا الجنائية' => [
                'الجرائم العامة' => ['دعوى قتل عمد', 'دعوى اعتداء جسدي'],
                'قضايا التعاطي والمخدرات' => ['دعوى تعاطي مخدرات', 'دعوى ترويج مخدرات'],
                'قضايا السرقة والاحتيال' => ['دعوى سرقة', 'دعوى احتيال مالي'],
            ],
            'القضايا التجارية' => [
                'نزاعات الشركات' => ['دعوى فض شراكة', 'دعوى نزاع على العلامة التجارية'],
                'الإفلاس والتصفية' => ['دعوى إعلان إفلاس', 'دعوى تصفية شركة'],
                'المنازعات البنكية' => ['دعوى نزاع على قرض', 'دعوى نزاع بنكي'],
            ],
            'قضايا الأحوال الشخصية' => [
                'الزواج والطلاق' => ['دعوى طلاق', 'دعوى خلع'],
                'حضانة الأطفال' => ['دعوى حضانة طفل', 'دعوى تسليم طفل'],
                'النفقة' => ['دعوى نفقة زوجية', 'دعوى نفقة أطفال'],
            ],
            'القضايا العمالية' => [
                'فصل تعسفي' => ['دعوى تعويض عن فصل تعسفي', 'دعوى إعادة إلى العمل'],
                'مستحقات نهاية الخدمة' => ['دعوى مستحقات نهاية الخدمة'],
                'عقود العمل' => ['دعوى نزاع على عقد العمل'],
            ],
        ];

        foreach ($categories as $categoryName => $subcategories) {
            $category = SettingsCategories::create([
                'name' => $categoryName,
                'status' => 'active',
                'user_id' => '1',
            ]);

            foreach ($subcategories as $subcategoryName => $lawsuitTypes) {
                $subcategory = SettingsSubcategories::create([
                    'category_id' => $category->id,
                    'name' => $subcategoryName,
                    'status' => 'active',
                    'user_id' => '1',
                ]);

                foreach ($lawsuitTypes as $lawsuitTypeName) {
                    SettingsLawsuitsType::create([
                        'subcategory_id' => $subcategory->id,
                        'name' => $lawsuitTypeName,
                        'status' => 'active',
                        'user_id' => '1',
                    ]);
                }
            }
        }
    }
}
