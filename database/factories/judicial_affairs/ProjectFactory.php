<?php

namespace Database\Factories\judicial_affairs;

use App\Models\Hr\Employees\Employees;
use App\Models\judicial_affairs\Opponent;
use App\Models\judicial_affairs\PowerOfAttorney;
use App\Models\judicial_affairs\Project;
use App\Models\OperationsCenter\Contract\Contract;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\judicial_affairs\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Project::class;

    public function definition()
    {

        // الحصول على قائمة معرفات من الجداول المرتبطة
        $employeeIds = Employees::pluck('id')->toArray();
        $contractIds = Contract::pluck('id')->toArray();
        $userIds = User::pluck('id')->toArray();

        // $powerOfAttorneyIds = PowerOfAttorney::pluck('id')->toArray();

        // $contractIds = Contract::pluck('id')->toArray();


        $projects = [
            [
                'name' => 'مشروع مكافحة الاحتيال المالي',
                'description' => 'يهدف هذا المشروع إلى تطوير نظم وتقنيات حديثة للكشف عن ومنع عمليات الاحتيال المالي في المؤسسات البنكية والشركات التجارية، مما يعزز من أمن النظام المالي ويقلل من الخسائر المالية.',
            ],
            [
                'name' => 'مشروع تعزيز الأمن السيبراني',
                'description' => 'يركز هذا المشروع على بناء وتعزيز البنية التحتية للأمن السيبراني في المؤسسات الحكومية والخاصة، مع توفير تدريب متخصص للعاملين لمواجهة التهديدات الإلكترونية المتطورة.',
            ],
            [
                'name' => 'مشروع تطوير شبكة المياه والصرف الصحي',
                'description' => 'يسعى المشروع إلى توسيع وتحسين شبكة توزيع المياه ومعالجة مياه الصرف الصحي في المناطق الحضرية والريفية، مما يساهم في تحسين جودة الحياة والصحة العامة.',
            ],
            [
                'name' => 'مشروع إنشاء مجمع سكني مستدام',
                'description' => 'يهدف المشروع إلى بناء مجمع سكني يتبنى مبادئ الاستدامة البيئية، مع توفير مرافق حديثة تشمل المساحات الخضراء، والمناطق الترفيهية، والمراكز التجارية لتلبية احتياجات السكان.',
            ],
            [
                'name' => 'مشروع إنشاء مصنع الإلكترونيات المتقدمة',
                'description' => 'يركز هذا المشروع على تأسيس مصنع حديث لإنتاج الأجهزة الإلكترونية المتقدمة، مع استخدام تقنيات التصنيع الذكية لضمان الجودة والكفاءة الإنتاجية.',
            ],
            [
                'name' => 'مشروع تطوير مركز تجاري متعدد الأغراض',
                'description' => 'يهدف المشروع إلى إنشاء مركز تجاري شامل يقدم مجموعة متنوعة من الخدمات والمنتجات، بالإضافة إلى مساحات للفعاليات الثقافية والترفيهية، مما يعزز من تجربة التسوق للمستهلكين.',
            ],
            [
                'name' => 'مشروع إنشاء محطة معالجة النفايات الصلبة',
                'description' => 'يهدف المشروع إلى بناء محطة متطورة لمعالجة النفايات الصلبة، مع استخدام تقنيات إعادة التدوير وتحويل النفايات إلى موارد قابلة للاستخدام، مما يساهم في الحفاظ على البيئة وتقليل التلوث.',
            ],
            [
                'name' => 'مشروع حماية الغابات والتنوع البيولوجي',
                'description' => 'يركز هذا المشروع على تنفيذ برامج لحماية الغابات وزيادة التنوع البيولوجي، مع تعزيز الوعي المجتمعي بأهمية الحفاظ على الموارد الطبيعية.',
            ],
            [
                'name' => 'مشروع بناء جامعة تقنية حديثة',
                'description' => 'يهدف المشروع إلى إنشاء جامعة متخصصة في المجالات التقنية والهندسية، مع توفير مرافق تعليمية وأبحاث علمية متقدمة لدعم الابتكار والبحث العلمي.',
            ],
            [
                'name' => 'مشروع تطوير الحرم الجامعي',
                'description' => 'يشمل المشروع ترميم وتوسعة الحرم الجامعي، إضافة مرافق تعليمية وترفيهية جديدة، وتحسين بيئة التعلم للطلاب لتعزيز جودة التعليم والبحث العلمي.',
            ],
        ];

        $selectedProject = $this->faker->randomElement($projects);
        // توليد تواريخ


        // حساب مدة المشروع إذا كانت التواريخ موجودة

        if (!empty($contractIds)) {
            $contract = Contract::find($this->faker->randomElement($contractIds));

            return [
                'project_name' => $selectedProject['name'],
                'employee_id' => $this->faker->randomElement($employeeIds),
                'start_date' => $contract->contract_start_date ?? null,
                'end_date' => '', // Populate if necessary
                // 'duration' => $duration, // Calculate duration if needed
                'description' => $selectedProject['description'],
                'total_claim' => $this->faker->optional()->randomFloat(2, 1000, 100000),
                'contractual_closure' => $contract->expected_closure_date ?? null,
                'opponent_proof_number' => $this->faker->boolean(50) ? $this->faker->unique()->numerify('########') : null,
                'status' => $this->faker->randomElement(['ongoing', 'completed', 'postponed', 'canceled', 'closed']),
                'complate_user_id' => $this->faker->optional()->randomElement($userIds),
                // 'power_of_attorney_id' => $this->faker->randomElement($powerOfAttorneyIds),
                'contract_id' => $contract->id,
            ];
        } else {
            throw new Exception("No contract IDs found. Please ensure there are contracts in the database.");
        }
    }
}
