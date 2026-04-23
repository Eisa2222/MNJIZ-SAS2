<?php

namespace Database\Factories\judicial_affairs;

use App\Models\general_setting\SettingsEntityRank;
use App\Models\Hr\Employees\Employees;
use App\Models\judicial_affairs\Project;
use App\Models\judicial_affairs\Session;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\judicial_affairs\Session>
 */
class SessionFactory extends Factory
{
    protected $model = Session::class;

    public function definition()
    {
        $entityRankIds = SettingsEntityRank::pluck('id')->toArray();
        // قائمة أسماء الدورات
        $sessionNames = [
            'جلسة افتتاحية',
            'جلسة استماع الشهود',
            'جلسة تقديم الأدلة',
            'جلسة المرافعات النهائية',
            'جلسة إصدار الحكم',
            'جلسة تنفيذ الحكم',
            'جلسة مراجعة القضية',
            'جلسة التوفيق والصلح',
            'جلسة التحكيم',
            'جلسة الوساطة',
        ];

        // قائمة حالات محكمة البرهان
        $alBurhanCourtStatuses = [
            'لا حاجة',
            'معتمدة',
            'انتظار التعميد',
        ];

        // قائمة حالات التقرير الإجمالي
        $summaryReportStatuses = [
            'مرفوعة للنظر',
            'لا يوجد',
            'شطب الدعوى',
            'الصلح',
            'وقف السير',
            'تأجيل',
            'حفظ الدعوى',
            'حكم موضوعي',
            'تأييد الحكم',
        ];

        // قائمة طرق إرسال التقرير
        $reportSendingMethods = [
            'مرسل بريديا',
            'مرسل هاتفيا',
            'مرسل عبر الواتساب',
            'مرسل بوسيلة اخرى',
            'غير مرسل',
        ];

        // قائمة وصفاء التقرير التفصيلي
        $detailedReports = [
            'تمت مناقشة كافة جوانب القضية بالتفصيل، وتم استعراض الأدلة والشهادات المقدمة.',
            'تضمن التقرير تفاصيل دقيقة حول سير الجلسة والأدلة التي تم تقديمها من كلا الطرفين.',
            'تم التركيز على النقاط الرئيسية للقضية وإجراء مناقشات مستفيضة حولها.',
            'شمل التقرير استعراضاً شاملاً للدفوع والحجج المقدمة من الطرفين.',
            'تمت مناقشة كافة التفاصيل المتعلقة بالقضية، مع التركيز على الإجراءات القانونية المتبعة.',
        ];

        // توليد تاريخ هجري عشوائي
        $hijriDate = $this->faker->date('d/m/Y');

        return [
            'session_name' => $this->faker->randomElement($sessionNames),
            // 'assigned_to' => Employees::inRandomOrder()->first()->id,
            'hijri_date' => $hijriDate,
            'project_id' => Project::inRandomOrder()->first()->id,
            'lawsuit_id' => Lawsuit::inRandomOrder()->first()->id,
            // 'al_burhan_court_status' => $this->faker->randomElement($alBurhanCourtStatuses),
            'detailed_report' => $this->faker->randomElement($detailedReports),
            'summary_report_status' => $this->faker->randomElement($summaryReportStatuses),
            'execution_minutes' => $this->faker->optional()->numberBetween(30, 180),
            'report_sending_method' => $this->faker->randomElement($reportSendingMethods),
            'entity_ranks_id' => $this->faker->randomElement($entityRankIds),

        ];
    }


    public function configure()
    {
        return $this->afterCreating(function (Session $session) {
            // تحديد عدد المكلفين لكل جلسة (من 1 إلى 3)
            $numberOfAssigned = rand(1, 3);

            // اختيار موظفين عشوائيين من جدول الموظفين
            $employees = Employees::inRandomOrder()->take($numberOfAssigned)->get();

            // ربط الموظفين بالجلسة عبر جدول assigned_sessions
            foreach ($employees as $employee) {
                // يمكنك تحديد user_id بطريقة مناسبة، هنا سأستخدم 1 كمثال
                $session->assignedEmployees()->attach($employee->id, ['user_id' => 1]);
            }
        });
    }
}
