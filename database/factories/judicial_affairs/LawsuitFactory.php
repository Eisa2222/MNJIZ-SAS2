<?php

namespace Database\Factories\judicial_affairs;

use App\Models\general_setting\SettingsCategories;
use App\Models\general_setting\SettingsDepartmentContractCase;
use App\Models\general_setting\SettingsEntity;
use App\Models\general_setting\SettingsEntityRank;
use App\Models\general_setting\SettingsLawsuitsType;
use App\Models\general_setting\SettingsMainCourt;
use App\Models\general_setting\SettingsRegion;
use App\Models\general_setting\SettingsSubcategories;
use App\Models\judicial_affairs\Opponent;
use App\Models\judicial_affairs\PowerOfAttorney;
use App\Models\judicial_affairs\Project;
use Illuminate\Database\Eloquent\Factories\Factory;


class LawsuitFactory extends Factory
{
    protected $model = Lawsuit::class;


    public function definition()
    {
        $departmentContractCaseIds = SettingsDepartmentContractCase::pluck('id')->toArray();
        $projectIds = Project::pluck('id')->toArray();
        $entityIds = SettingsEntity::pluck('id')->toArray();
        $mainCourtIds = SettingsMainCourt::pluck('id')->toArray();
        $entityRankIds = SettingsEntityRank::pluck('id')->toArray();
        $regionIds = SettingsRegion::pluck('id')->toArray();
        $opponentIds = Opponent::pluck('id')->toArray();
        $categoryIds = SettingsCategories::pluck('id')->toArray();
        $subcategoryIds = SettingsSubcategories::pluck('id')->toArray();
        $lawsuitTypeIds = SettingsLawsuitsType::pluck('id')->toArray();
        $powerOfAttorneyIds = PowerOfAttorney::pluck('id')->toArray();

        // توليد رقم دعوى فريد
        $lawsuitNumber = $this->faker->unique()->regexify('\d{6}');

        // توليد نطاق الدائرة القانونية
        $circles = [
            'الدائرة الأولى',
            'الدائرة الثانية',
            'الدائرة الثالثة',
            'الدائرة الرابعة',
            'الدائرة الخامسة',
        ];

        // توليد نصوص مرفقات الدعوى
        $lawsuitAttachments = [
            'صحيفة دعوى مرفقة',
            'مستندات إضافية مرفقة',
            'ملف قانوني مرفق',
            'دليل تقديم الدعوى',
            'أدلة إضافية مرفقة',
        ];

        // توليد نصوص برهاننا
        $ourProofs = [
            'برهاننا المتمثل في المستندات التالية...',
            'نستند في برهاننا إلى الأدلة المقدمة...',
            'برهاننا يعتمد على الشهادات والمستندات المرفقة...',
            'نحن نملك دليل قوي على صحة ادعائنا...',
            'برهاننا يستند إلى الوقائع التالية...',
        ];

        // توليد نصوص برهان الخصم
        $opponentProofs = [
            'برهان الخصم المتمثل في المستندات التالية...',
            'الخصم يستند في برهانه إلى الأدلة المقدمة...',
            'برهان الخصم يعتمد على الشهادات والمستندات المرفقة...',
            'الخصم يملك دليلًا قويًا على صحة ادعائه...',
            'برهان الخصم يستند إلى الوقائع التالية...',
        ];

        // توليد نصوص موضوع الدعوى، طلبات المدعي، وأسانيد الدعوى
        $lawsuitSubjects = [
            'موضوع الدعوى يتعلق بمسائل قانونية...',
            'الدعوى تناقش انتهاك حقوق الملكية...',
            'مطالبات مالية وتحصيل مستحقات...',
            'إجراءات نزاع بين الأطراف...',
            'قضية متعلقة بمسائل حضانة...',
        ];

        $plaintiffRequests = [
            'المدعي يطالب بتعويض مالي...',
            'المدعي يطالب بفسخ العقد...',
            'المدعي يطلب الحضانة...',
            'المدعي يطالب بإعادة الممتلكات...',
            'المدعي يطلب تسوية النزاع...',
        ];

        $lawsuitProofs = [
            'أسانيد الدعوى تشمل المستندات التالية...',
            'الشهادات المرفقة تدعم ادعاءاتنا...',
            'الدعوى مدعومة بالأدلة التالية...',
            'أسانيد الدعوى تستند إلى القوانين والأنظمة...',
            'نستند في الدعوى إلى المستندات القانونية المرفقة...',
        ];

        $lawsuitNames = [
            // دعاوى جنائية
            'دعوى السرقة الكبرى',
            'دعوى الاحتيال المالي',
            'دعوى التزوير والتزوير المالي',
            'دعوى الاعتداء العنيف',
            'دعوى الاتجار بالمخدرات',
            'دعوى الإرهاب والجرائم المنظمة',
            'دعوى خيانة الأمانة',
            'دعوى الجرائم الإلكترونية',
            'دعوى الاحتيال العقاري',
            'دعوى الجرائم البيئية',

            // دعاوى مدنية
            'دعوى تعويض الأضرار الناتجة عن الإهمال',
            'دعوى فسخ عقد البيع',
            'دعوى استرجاع الملكية العقارية',
            'دعوى حل النزاعات التجارية',
            'دعوى الحضانة والطلاق',
            'دعوى حقوق المستهلك',
            'دعوى تسوية الديون',
            'دعوى فسخ عقد العمل',
            'دعوى حماية الملكية الفكرية',
            'دعوى الامتثال التنظيمي',

            // دعاوى تجارية
            'دعوى خرق العقود التجارية',
            'دعوى المنافسة غير العادلة',
            'دعوى حماية العلامات التجارية',
            'دعوى تسوية النزاعات الاستثمارية',
            'دعوى فشل تنفيذ المشاريع',
            'دعوى تضليل المستهلك',
            'دعوى استعادة الأصول التجارية',
            'دعوى الإفلاس والتصفية',
            'دعوى الامتثال للقوانين التجارية',
            'دعوى توزيع الأرباح',
        ];
        $name = $this->faker->randomElement($lawsuitNames);

        return [
            'name' => $name,
            'lawsuit_number' => $lawsuitNumber,
            'department_contract_cases_id' => $this->faker->randomElement($departmentContractCaseIds),
            'project_id' => $this->faker->randomElement($projectIds),
            'entitie_id' => $this->faker->randomElement($entityIds),
            'main_courts_id' => $this->faker->randomElement($mainCourtIds),
            // 'entity_ranks_id' => $this->faker->randomElement($entityRankIds),
            'regions_id' => $this->faker->randomElement($regionIds),
            'circle' => $this->faker->randomElement($circles),
            'lawsuit_attachment' => $this->faker->optional()->randomElement($lawsuitAttachments),
            'opponent_proof' => $this->faker->optional()->randomElement($opponentProofs),
            // 'plaintiff_id' => $this->faker->randomElement($opponentIds), // افتراضًا استخدام معرف الخصم هنا
            // 'plaintiff_type' => $this->faker->randomElement(['App\Models\business_development\Customers', 'App\Models\judicial_affairs\Opponent']),
            // 'defendant_id' => $this->faker->randomElement($opponentIds),
            // 'defendant_type' => $this->faker->randomElement(['App\Models\business_development\Customers', 'App\Models\judicial_affairs\Opponent']),
            'category_id' => $this->faker->randomElement($categoryIds),
            'subcategory_id' => $this->faker->randomElement($subcategoryIds),
            'lawsuit_type_id' => $this->faker->randomElement($lawsuitTypeIds),
            'lawsuit_subject' => $this->faker->optional()->randomElement($lawsuitSubjects),
            'plaintiff_requests' => $this->faker->optional()->randomElement($plaintiffRequests),
            'lawsuit_proofs' => $this->faker->optional()->randomElement($lawsuitProofs),
        ];
    }

    public function withPowerOfAttorneys($count = 3)
    {
        return $this->afterCreating(function (Lawsuit $lawsuit) use ($count) {
            $powerOfAttorneys = PowerOfAttorney::inRandomOrder()->take($count)->pluck('id');
            $lawsuit->powerOfAttorneys()->sync($powerOfAttorneys);
        });
    }
}
