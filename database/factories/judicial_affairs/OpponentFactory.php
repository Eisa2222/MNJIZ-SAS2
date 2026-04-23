<?php

namespace Database\Factories\judicial_affairs;

use App\Models\general_setting\SettingsRegion;
use App\Models\judicial_affairs\Opponent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\judicial_affairs\Opponent>
 */
class OpponentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Opponent::class;

    public function definition()
    {

        $biosIndividual = [
            'محامي متفوق في قضايا الملكية الفكرية والتجارية، يمتلك سجلًا حافلًا في تقديم الاستشارات القانونية.',
            'محامية خبيرة في قضايا الأسرة والتخطيط العقاري، تقدم حلولًا قانونية متميزة للأفراد.',
            'مستشار قانوني متخصص في قوانين العمل والتوظيف، يقدم الدعم القانوني للموظفين وأصحاب العمل.',
            'محامي دفاع جنائي معروف بمهاراته الفائقة في الدفاع عن موكليه في المحاكم.',
            'خبير قانوني في قضايا الهجرة والقوانين الدولية، يساعد الأفراد في تحقيق أهدافهم القانونية.',
            'محامي استشاري في قضايا النزاعات العائلية والتجارية، يقدم حلولًا فعالة وودية.',
            'محامية متمرسة في صياغة العقود واتفاقيات العمل، تضمن حقوق موكليها القانونية.',
            'مستشار قانوني متخصص في حماية البيانات والخصوصية، يقدم استشارات للأفراد والشركات.',
            'محامي متخصص في قانون الملكية الفكرية، يساعد الأفراد في حماية إبداعاتهم وحقوقهم.',
            'محامية دفاع جنائي تقدم خدمات قانونية متكاملة للأفراد المتهمين في القضايا الجنائية.',
        ];

        $biosCompany = [
            'شركة قانونية رائدة تقدم خدمات استشارية شاملة للشركات والمؤسسات الكبرى.',
            'مؤسسة قانونية متخصصة في قضايا التحكيم والوساطة، تساعد الشركات في حل النزاعات بشكل ودي.',
            'شركة قانونية تقدم خدمات إعداد وصياغة العقود التجارية المعقدة لضمان حقوق الشركات.',
            'مؤسسة قانونية متخصصة في قوانين العمل والتوظيف، تقدم استشارات للشركات في إدارة الموارد البشرية.',
            'شركة قانونية تقدم خدمات قانونية متكاملة للشركات الناشئة في مختلف القطاعات.',
            'مؤسسة قانونية تقدم خدمات الدفاع القانوني للشركات في المحاكم وتسوية النزاعات التجارية.',
            'شركة قانونية متخصصة في حماية الملكية الفكرية للشركات، تضمن حقوق الابتكار والإبداع.',
            'مؤسسة قانونية تقدم خدمات مراجعة اللوائح الداخلية والإجراءات القانونية للشركات.',
            'شركة قانونية تقدم خدمات قانونية في مجالات الضرائب والتراخيص التجارية للشركات.',
            'مؤسسة قانونية تقدم خدمات استشارات قانونية للشركات في قضايا الامتثال والتنظيم.',
        ];

        // تحديد نوع الخصم: فرد أو مؤسسة
        $type = $this->faker->randomElement(['individual', 'company']);

        // توليد رقم هوية فريد إذا كان النوع فرد
        $identityNumber = $type === 'individual' ? $this->faker->unique()->numerify('#########') : null;

        // توليد سجل تجاري ورقم موحد إذا كان النوع مؤسسة
        $commercialRegistration = $type === 'company' ? $this->faker->unique()->regexify('[A-Z0-9]{10}') : null;
        $unifiedNumber = $type === 'company' ? $this->faker->unique()->numerify('#########') : null;

        $settings_region_id = SettingsRegion::pluck('id')->toArray();

        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->numerify('05########'), // رقم هاتف بدون شرطات
            'bio' => $type === 'individual'
                ? $this->faker->optional()->randomElement($biosIndividual)
                : $this->faker->optional()->randomElement($biosCompany),
            'settings_region_id' => $this->faker->randomElement($settings_region_id),
            'type' => $type,
            'commercial_registration' => $commercialRegistration,
            'unified_number' => $unifiedNumber,
            'identity_number' => $identityNumber,
        ];
    }
}
