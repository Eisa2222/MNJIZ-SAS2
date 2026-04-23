<?php

namespace Database\Factories\Hr\employees;

use App\Models\general_setting\SettingsCountry;
use App\Models\general_setting\SettingsHRClassification;
use App\Models\general_setting\SettingsHrStatus;
use App\Models\Hr\Employees\Employees;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Hr\Employees\Employees>
 */
class EmployeesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Employees::class;

    public function definition()
    {
        $bios = [
            'محامي مختص في القانون التجاري يمتلك خبرة واسعة في صياغة العقود وحل النزاعات التجارية.',
            'محامي دفاع جنائي يتمتع بمهارات قوية في تقديم الدفاعات القانونية في القضايا الجنائية المعقدة.',
            'مستشار قانوني متخصص في الملكية الفكرية وحماية حقوق النشر والعلامات التجارية.',
            'محامي شركات يقدم الاستشارات القانونية في مجال الشركات والامتثال القانوني.',
            'مدير موارد بشرية يمتلك خبرة في تطوير سياسات التوظيف وإدارة الأداء.',
            'مسؤول توظيف متمرس يعمل على اختيار وتعيين أفضل الكفاءات ضمن فرق العمل.',
            'مديرة شؤون الموظفين متخصصة في إدارة العلاقات الوظيفية وتطوير خطط النمو المهني.',
            'محامي متخصص في قضايا الأحوال الشخصية والوصايا والميراث.',
            'محامي متخصص في التحكيم التجاري وتسوية النزاعات خارج المحكمة.',
            'مدير تنفيذي للموارد البشرية، لديه مهارات قيادية في وضع استراتيجيات الموارد البشرية والتخطيط للموارد.',
            'محامي عقارات يمتلك خبرة واسعة في إدارة العقود العقارية وتسوية النزاعات المتعلقة بها.',
            'مستشار قانوني للشركات يقدم الحلول القانونية المتكاملة لإدارة العمليات القانونية للشركات الكبرى.',
            'خبير موارد بشرية يعمل على تطوير برامج تدريب وتطوير المهارات للموظفين.',
            'محامي متخصص في القانون الإداري ويقدم الاستشارات في المنازعات مع الهيئات الحكومية.',
            'مسؤول علاقات الموظفين يعمل على تعزيز بيئة العمل الإيجابية وإدارة شؤون الموظفين اليومية.'
        ];




        $countryIds = SettingsCountry::pluck('id')->toArray();
        $hrStatusIds = SettingsHrStatus::pluck('id')->toArray();
        $hrClassificationIds = SettingsHRClassification::pluck('id')->toArray();

        // إنشاء مستخدم مرتبط بالموظف
        $user = User::factory()->create([
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'), // يمكنك تغيير كلمة المرور كما ترغب
            'phone' => $this->faker->numerify('05########'),
            'status' => $this->faker->randomElement(['active', 'inactive']), // يمكنك استخدام القيم المناسبة لحقل الحالة
            'nationality' => $this->faker->randomElement($countryIds), // أو أي جنسية أخرى
            'job' => $this->faker->jobTitle(),
            // 'image' => 'default.png',
        ]);

        return [
            // البيانات الشخصية
            'name' => $user->name,
            'nationality' => $this->faker->randomElement($countryIds),
            'id_number' => $this->faker->unique()->numerify('############'),
            'personal_email' => $this->faker->unique()->safeEmail(),
            'work_email' => $user->email,
            'mobile' => $user->phone,
            'address' => $this->faker->address(),
            'birth_date' => $this->faker->date(),

            // معلومات الوظيفة
            'hr_status_id' => $this->faker->optional()->randomElement($hrStatusIds),

            'job_title' => $this->faker->randomElement(['شريك مؤسس', 'رئيس تنفيذي', 'محامي', 'محامي متدرب', 'مستشار']),
            'license_type' => $this->faker->optional()->randomElement(['رخصة محامي', 'رخصة محامي متدرب']),
            'insurance_status' => $this->faker->randomElement(['مضاف', 'مضاف غير رسمي', 'مستبعد', 'غير مسجل']),
            'contract_start_date' => $this->faker->optional()->date(),
            'contract_end_date' => $this->faker->optional()->date(),
            'training_end_date' => $this->faker->optional()->date(),

            // الرواتب والبدلات
            'basic_salary' => $this->faker->optional()->randomFloat(2, 5000, 20000),
            'transportation_allowance' => $this->faker->optional()->randomFloat(2, 500, 2000),
            'housing_allowance' => $this->faker->optional()->randomFloat(2, 1000, 5000),
            'other_allowances' => $this->faker->optional()->randomFloat(2, 0, 10000),

            // معلومات أخرى
            'training_number' => $this->faker->optional()->numerify('####'),
            'national_number' => $this->faker->optional()->numerify('#'),
            // 'qualification_degree' => $this->faker->optional()->randomElement(['بكلاريوس', 'ماجستير', 'دكتوراه']),
            'bio' => $this->faker->randomElement($bios),
            'vacation_balance' => $this->faker->optional()->numberBetween(0, 30),
            // 'business_card' => $this->faker->optional()->imageUrl(),
            'knowledge_area' => $this->faker->optional()->randomElement([
                'التركات والوصايا',
                'الملكية الفكرية',
                'العقارات',
                'الاوقاف',
                'الاستشارات الحكومية والتشريعات والتخصيص',
            ]),





            // بيانات الدخول للنظام
            'user_id' => $user->id,

            // تصنيف الموارد البشرية
            'hr_classification_id' => $this->faker->optional()->randomElement($hrClassificationIds),

        ];
    }
}
