<?php

namespace Database\Seeders;

use App\Models\general_setting\SettingsViolation;
use App\Models\general_setting\SettingsViolationCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsViolationSeeder extends Seeder
{
    /**
     * تشغيل Seeder.
     */
    public function run(): void
    {
        // تعطيل قيود المفتاح الخارجي لإعادة تعبئة البيانات دون مشاكل
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        SettingsViolation::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');


        // تعريف جميع سجلات المخالفات كما وردت في البيانات
        $violations = [
            //
            [
                'description'       => 'التأخر عن مواعيد الحضور للعمل لغاية (15) دقيقة دون إذن، أو عذر مقبول: إذا لم يترتب على ذلك تعطيل عمال آخرين.',
                'category'          =>  1,
                'penalty_first'     => 'warning:written',
                'penalty_second'    => 'percentage:5',
                'penalty_third'     => 'percentage:10',
                'penalty_fourth'    => 'percentage:20',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'delay',
                'duration_unit'     => 'minutes',
                'duration_from'     => 1,
                'duration_to'       => 15,
            ],

            [
                'description'       => 'التأخر عن مواعيد الحضور للعمل لغاية (15) دقيقة دون إذن، أو عذر مقبول؛ إذا ترتب على ذلك تعطيل عمال آخرين.',
                'category'          =>  1,
                'penalty_first'     => 'warning:written',
                'penalty_second'    => 'percentage:15',
                'penalty_third'     => 'percentage:25',
                'penalty_fourth'    => 'percentage:50',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'delay',
                'duration_unit'     => 'minutes',
                'duration_from'     => 1,
                'duration_to'       => 15,

            ],

            [
                'description'       => 'التأخر عن مواعيد الحضور للعمل أكثر من (15) دقيقة لغاية (30) دقيقة دون إذن، أو عذر مقبول؛ إذا لم يترتب على ذلك تعطيل عمال آخرين.',
                'category'          =>  1,
                'penalty_first'     => 'percentage:10',
                'penalty_second'    => 'percentage:15',
                'penalty_third'     => 'percentage:25',
                'penalty_fourth'    => 'percentage:50',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'delay',
                'duration_unit'     => 'minutes',
                'duration_from'     => 16,
                'duration_to'       => 30,

            ],

            [
                'description'       => 'التأخر عن مواعيد الحضور للعمل أكثر من (15) دقيقة لغاية (30) دقيقة دون إذن، أو عذر مقبول؛ إذا ترتب على ذلك تعطيل عمال آخرين.',
                'category'          =>  1,
                'penalty_first'     => 'percentage:25',
                'penalty_second'    => 'percentage:50',
                'penalty_third'     => 'percentage:75',
                'penalty_fourth'    => 'days:1',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'delay',
                'duration_unit'     => 'minutes',
                'duration_from'     => 16,
                'duration_to'       => 30,

            ],

            [
                'description'       => 'التأخر عن مواعيد الحضور للعمل أكثر من (30) دقيقة لغاية (60) دقيقة دون إذن، أو عذر مقبول؛ إذا لم يترتب على ذلك تعطيل عمال آخرين.',
                'category'          =>  1,
                'penalty_first'     => 'percentage:25',
                'penalty_second'    => 'percentage:50',
                'penalty_third'     => 'percentage:75',
                'penalty_fourth'    => 'days:1',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'delay',
                'duration_unit'     => 'minutes',
                'duration_from'     => 31,
                'duration_to'       => 60,
            ],

            [
                'description'       => 'التأخر عن مواعيد الحضور للعمل أكثر من (30) دقيقة لغاية (60) دقيقة دون إذن، أو عذر مقبول؛ إذا ترتب على ذلك تعطيل عمال آخرين.',
                'category'          =>  1,
                'penalty_first'     => 'percentage:30',
                'penalty_second'    => 'percentage:50',
                'penalty_third'     => 'days:1',
                'penalty_fourth'    => 'days:2',
                'extra_deduction'   => 'بالإضافة إلى حسم أجر دقائق التأخر',
                'user_id'           => 1,

                'violation_type'    => 'delay',
                'duration_unit'     => 'minutes',
                'duration_from'     => 31,
                'duration_to'       => 60,
            ],

            [
                'description'       => 'التأخر عن مواعيد الحضور للعمل لمدة تزيد على ساعة دون إذن، أو عذر مقبول؛ سواء ترتب، أو لم يترتب على ذلك تعطيل عمال آخرين.',
                'category'          =>  1,
                'penalty_first'     => 'warning:written',
                'penalty_second'    => 'days:1',
                'penalty_third'     => 'days:2',
                'penalty_fourth'    => 'days:3',
                'extra_deduction'   => 'بالإضافة إلى حسم أجر ساعات التأخر',
                'user_id'           => 1,

                'violation_type'    => 'delay',
                'duration_unit'     => 'hours',
                'duration_from'     => 1,
                'duration_to'       => null,
            ],

            [
                'description'       => 'ترك العمل، أو الانصراف قبل الميعاد دون إذن، أو عذر مقبول بما لا يتجاوز (15) دقيقة',
                'category'          =>  1,
                'penalty_first'     => 'warning:written',
                'penalty_second'    => 'percentage:10',
                'penalty_third'     => 'percentage:25',
                'penalty_fourth'    => 'days:1',
                'extra_deduction'   => 'بالإضافة إلى حسم أجر مدة ترك العمل',
                'user_id'           => 1,

                'violation_type'    => 'early_leave',
                'duration_unit'     => 'minutes',
                'duration_from'     => 1,
                'duration_to'       => 15,
            ],

            [
                'description'       => 'ترك العمل، أو الانصراف قبل الميعاد دون إذن، أو عذر مقبول بما يتجاوز (15) دقيقة.',
                'category'          =>  1,
                'penalty_first'     => 'percentage:10',
                'penalty_second'    => 'percentage:25',
                'penalty_third'     => 'percentage:50',
                'penalty_fourth'    => 'days:1',
                'extra_deduction'   => 'بالإضافة إلى حسم أجر مدة ترك العمل',
                'user_id'           => 1,

                'violation_type'    => 'early_leave',
                'duration_unit'     => 'minutes',
                'duration_from'     => 1,
                'duration_to'       => 15,

            ],

            [
                'description'       => 'البقاء في أماكن العمل، أو العودة إليها بعد انتهاء مواعيد العمل دون إذن مسبق.',
                'category'          =>  1,
                'penalty_first'     => 'warning:written',
                'penalty_second'    => 'percentage:10',
                'penalty_third'     => 'percentage:25',
                'penalty_fourth'    => 'days:1',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'after_hours',
                'duration_unit'     => 'hours',
                'duration_from'     => 1,
                'duration_to'       => null,
            ],

            [
                'description'       => 'الغياب دون إذن كتابي، أو عذر مقبول لمدة يوم، خلال السنة العقدية الواحدة.',
                'category'          =>  1,
                'penalty_first'     => 'days:2',
                'penalty_second'    => 'days:3',
                'penalty_third'     => 'days:4',
                'penalty_fourth'    => 'ban:promotion',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'absence',
                'duration_unit'     => 'days',
                'duration_from'     => 1,
                'duration_to'       => 1,

            ],

            [
                'description'       => 'الغياب المتصل دون إذن كتابي، أو عذر مقبول من يومين إلى ستة أيام، خلال السنة العقدية الواحدة.',
                'category'          =>  1,
                'penalty_first'     => 'days:2',
                'penalty_second'    => 'days:3',
                'penalty_third'     => 'days:4',
                'penalty_fourth'    => 'ban:promotion',
                'extra_deduction'   => 'بالإضافة إلى حسم أجر مدة الغياب',
                'user_id'           => 1,

                'violation_type'    => 'absence',
                'duration_unit'     => 'days',
                'duration_from'     => 2,
                'duration_to'       => 6,
            ],

            [
                'description'       => 'الغياب المتصل دون إذن كتابي، أو عذر مقبول من سبعة أيام إلى عشرة أيام، خلال السنة العقدية الواحدة.',
                'category'          =>  1,
                'penalty_first'     => 'days:4',
                'penalty_second'    => 'days:5',
                'penalty_third'     => 'ban:promotion',
                'penalty_fourth'    => 'termination:with_benefit_30',
                'extra_deduction'   => 'بالإضافة إلى حسم أجر مدة الغياب',
                'user_id'           => 1,

                'violation_type'    => 'absence',
                'duration_unit'     => 'days',
                'duration_from'     => 7,
                'duration_to'       => 10,
            ],

            [
                'description'       => 'الغياب المتصل دون إذن كتابي، أو عذر مقبول من أحد عشر يوماً إلى أربعة عشر يوماً، خلال السنة العقدية الواحدة.',
                'category'          =>  1,
                'penalty_first'     => 'days:5',
                'penalty_second'    => 'ban:promotion',
                'penalty_third'     => 'termination:article_80',
                'penalty_fourth'    => null,
                'extra_deduction'   => 'بالإضافة إلى حسم أجر مدة الغياب',
                'user_id'           => 1,

                'violation_type'    => 'absence',
                'duration_unit'     => 'days',
                'duration_from'     => 11,
                'duration_to'       => 14,
            ],

            [
                'description'       => 'الانقطاع عن العمل دون سبب مشروع مدة تزيد على خمسة عشر يوماً متصلة، خلال السنة العقدية الواحدة.',
                'category'          =>  1,
                'penalty_first'     => 'termination:article_80_10days',
                'penalty_second'    => null,
                'penalty_third'     => null,
                'penalty_fourth'    => null,
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'absence',
                'duration_unit'     => 'days',
                'duration_from'     => 15,
                'duration_to'       => null,
            ],

            [
                'description'       => 'الغياب المتقطع عن العمل دون سبب مشروع مدداً تزيد في مجموعها على ثلاثين يوماً، خلال السنة العقدية الواحدة.',
                'category'          =>  1,
                'penalty_first'     => 'termination:article_80_20days',
                'penalty_second'    => null,
                'penalty_third'     => null,
                'penalty_fourth'    => null,
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'absence',
                'duration_unit'     => 'days',
                'duration_from'     => 31,
                'duration_to'       => null,
            ],
            // ثانياً: مخالفات تتعلق بتنظيم العمل
            [
                'description'       => 'التواجد دون مبرر في غير مكان العمل المخصص للعامل أثناء وقت الدوام.',
                'category'          =>  2,
                'penalty_first'     => 'percentage:10',
                'penalty_second'    => 'percentage:25',
                'penalty_third'     => 'percentage:50',
                'penalty_fourth'    => 'days:1',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'other',
                'duration_unit'     => null,
                'duration_from'     => null,
                'duration_to'       => null,
            ],

            [
                'description'       => 'استقبال زائرين في غير أمور عمل الشركة في أماكن العمل، دون إذن من الإدارة.',
                'category'          =>  2,
                'penalty_first'     => 'warning:written',
                'penalty_second'    => 'percentage:10',
                'penalty_third'     => 'percentage:15',
                'penalty_fourth'    => 'percentage:25',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'other',
                'duration_unit'     => null,
                'duration_from'     => null,
                'duration_to'       => null,
            ],

            [
                'description'       => 'استعمال آلات، ومعدات، وأدوات الشركة؛ لأغراض خاصة، دون إذن.',
                'category'          =>  2,
                'penalty_first'     => 'warning:written',
                'penalty_second'    => 'percentage:10',
                'penalty_third'     => 'percentage:25',
                'penalty_fourth'    => 'percentage:50',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'other',
                'duration_unit'     => null,
                'duration_from'     => null,
                'duration_to'       => null,
            ],

            [
                'description'       => 'تدخل العامل، دون وجه حق في أي عمل ليس في اختصاصه، أو لم يعهد به إليه.',
                'category'          =>  2,
                'penalty_first'     => 'percentage:50',
                'penalty_second'    => 'days:1',
                'penalty_third'     => 'days:2',
                'penalty_fourth'    => 'days:3',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'other',
                'duration_unit'     => null,
                'duration_from'     => null,
                'duration_to'       => null,
            ],

            [
                'description'       => 'الخروج، أو الدخول من غير المكان المخصص لذلك.',
                'category'          =>  2,
                'penalty_first'     => 'warning:written',
                'penalty_second'    => 'percentage:10',
                'penalty_third'     => 'percentage:15',
                'penalty_fourth'    => 'percentage:25',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'other',
                'duration_unit'     => null,
                'duration_from'     => null,
                'duration_to'       => null,
            ],

            [
                'description'       => 'الإهمال في تنظيف الآلات، وصيانتها، أو عدم العناية بها، أو عدم التبليغ عما بها من خلل.',
                'category'          =>  2,
                'penalty_first'     => 'percentage:50',
                'penalty_second'    => 'days:1',
                'penalty_third'     => 'days:2',
                'penalty_fourth'    => 'days:3',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'other',
                'duration_unit'     => null,
                'duration_from'     => null,
                'duration_to'       => null,
            ],

            [
                'description'       => 'عدم وضع أدوات الإصلاح، والصيانة، واللوازم الأخرى في الأماكن المخصصة لها، بعد الانتهاء من العمل.',
                'category'          =>  2,
                'penalty_first'     => 'warning:written',
                'penalty_second'    => 'percentage:25',
                'penalty_third'     => 'percentage:50',
                'penalty_fourth'    => 'days:1',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'other',
                'duration_unit'     => null,
                'duration_from'     => null,
                'duration_to'       => null,
            ],

            [
                'description'       => 'تمزيق، أو إتلاف إعلانات، أو بلاغات إدارة الشركة.',
                'category'          =>  2,
                'penalty_first'     => 'days:2',
                'penalty_second'    => 'days:3',
                'penalty_third'     => 'days:5',
                'penalty_fourth'    => 'termination:with_benefit',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'other',
                'duration_unit'     => null,
                'duration_from'     => null,
                'duration_to'       => null,
            ],

            [
                'description'       => 'الإهمال في العهد التي بحوزته، مثال: (سيارات، آلات، أجهزة، معدات، أدوات،... الخ).',
                'category'          =>  2,
                'penalty_first'     => 'days:2',
                'penalty_second'    => 'days:3',
                'penalty_third'     => 'days:5',
                'penalty_fourth'    => 'termination:with_benefit',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'other',
                'duration_unit'     => null,
                'duration_from'     => null,
                'duration_to'       => null,
            ],

            [
                'description'       => 'الأكل في مكان العمل، أو غير المكان المعد له، أو في غير أوقات الراحة.',
                'category'          =>  2,
                'penalty_first'     => 'warning:written',
                'penalty_second'    => 'percentage:10',
                'penalty_third'     => 'percentage:15',
                'penalty_fourth'    => 'percentage:25',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'other',
                'duration_unit'     => null,
                'duration_from'     => null,
                'duration_to'       => null,
            ],

            [
                'description'       => 'النوم أثناء العمل.',
                'category'          =>  2,
                'penalty_first'     => 'warning:written',
                'penalty_second'    => 'percentage:10',
                'penalty_third'     => 'percentage:25',
                'penalty_fourth'    => 'percentage:50',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'other',
                'duration_unit'     => null,
                'duration_from'     => null,
                'duration_to'       => null,
            ],

            [
                'description'       => 'النوم في الحالات التي تستدعي يقظة مستمرة.',
                'category'          =>  2,
                'penalty_first'     => 'percentage:50',
                'penalty_second'    => 'days:1',
                'penalty_third'     => 'days:2',
                'penalty_fourth'    => 'days:3',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'other',
                'duration_unit'     => null,
                'duration_from'     => null,
                'duration_to'       => null,
            ],

            [
                'description'       => 'التسكع، أو وجود العامل في غير مكان عمله، أثناء ساعات العمل.',
                'category'          =>  2,
                'penalty_first'     => 'percentage:10',
                'penalty_second'    => 'percentage:25',
                'penalty_third'     => 'percentage:50',
                'penalty_fourth'    => 'days:1',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'other',
                'duration_unit'     => null,
                'duration_from'     => null,
                'duration_to'       => null,
            ],
            [
                'description'       => 'التلاعب في إثبات الحضور، والانصراف.',
                'category'          =>  2,
                'penalty_first'     => 'days:1',
                'penalty_second'    => 'days:2',
                'penalty_third'     => 'ban:promotion',
                'penalty_fourth'    => 'termination:with_benefit',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'other',
                'duration_unit'     => null,
                'duration_from'     => null,
                'duration_to'       => null,
            ],
            [
                'description'       => 'عدم إطاعة الأوامر العادية الخاصة بالعمل، أو عدم تنفيذ التعليمات الخاصة بالعمل، والمعلقة في مكان ظاهر.',
                'category'          =>  2,
                'penalty_first'     => 'percentage:25',
                'penalty_second'    => 'percentage:50',
                'penalty_third'     => 'days:1',
                'penalty_fourth'    => 'days:2',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'other',
                'duration_unit'     => null,
                'duration_from'     => null,
                'duration_to'       => null,
            ],
            [
                'description'       => 'التحريض على مخالفة الأوامر، والتعليمات الخطية الخاصة بالعمل.',
                'category'          =>  2,
                'penalty_first'     => 'days:2',
                'penalty_second'    => 'days:3',
                'penalty_third'     => 'days:5',
                'penalty_fourth'    => 'termination:with_benefit',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'other',
                'duration_unit'     => null,
                'duration_from'     => null,
                'duration_to'       => null,
            ],
            [
                'description'       => 'التدخين في الأماكن المحظورة، والمعلن عنها للمحافظة على سلامة العمال، والشركة.',
                'category'          =>  2,
                'penalty_first'     => 'days:2',
                'penalty_second'    => 'days:3',
                'penalty_third'     => 'days:5',
                'penalty_fourth'    => 'termination:with_benefit',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'other',
                'duration_unit'     => null,
                'duration_from'     => null,
                'duration_to'       => null,
            ],
            [
                'description'       => 'الإهمال، أو التهاون في العمل الذي قد ينشأ عنه ضرر في صحة العمال، أو سلامتهم، أو في المواد، أو الأدوات، والأجهزة.',
                'category'          =>  2,
                'penalty_first'     => 'days:2',
                'penalty_second'    => 'days:3',
                'penalty_third'     => 'days:5',
                'penalty_fourth'    => 'termination:with_benefit',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'other',
                'duration_unit'     => null,
                'duration_from'     => null,
                'duration_to'       => null,
            ],
            // ثالثاً: مخالفات تتعلق بسلوك العامل
            [
                'description'       => 'التشاجر مع الزملاء أو مع الغير، أو إحداث مشاغبات في مكان العمل.',
                'category'          =>  3,
                'penalty_first'     => 'days:1',
                'penalty_second'    => 'days:2',
                'penalty_third'     => 'days:3',
                'penalty_fourth'    => 'days:5',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'other',
                'duration_unit'     => null,
                'duration_from'     => null,
                'duration_to'       => null,
            ],
            [
                'description'       => 'التمارض، أو ادعاء العامل كذبًا أنه أصيب أثناء العمل، أو بسببه.',
                'category'          =>  3,
                'penalty_first'     => 'days:1',
                'penalty_second'    => 'days:2',
                'penalty_third'     => 'days:3',
                'penalty_fourth'    => 'days:5',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'other',
                'duration_unit'     => null,
                'duration_from'     => null,
                'duration_to'       => null,
            ],
            [
                'description'       => 'الامتناع عن إجراء الكشف الطبي عند طلب طبيب الشركة، أو رفض اتباع التعليمات الطبية.',
                'category'          =>  3,
                'penalty_first'     => 'days:1',
                'penalty_second'    => 'days:2',
                'penalty_third'     => 'days:3',
                'penalty_fourth'    => 'days:5',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'other',
                'duration_unit'     => null,
                'duration_from'     => null,
                'duration_to'       => null,
            ],
            [
                'description'       => 'مخالفة التعليمات الصحية المعلقة بأماكن العمل.',
                'category'          =>  3,
                'penalty_first'     => 'percentage:50',
                'penalty_second'    => 'days:1',
                'penalty_third'     => 'days:2',
                'penalty_fourth'    => 'days:5',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'other',
                'duration_unit'     => null,
                'duration_from'     => null,
                'duration_to'       => null,
            ],
            [
                'description'       => 'الكتابة على جدران الشركة، أو لصق إعلانات عليها.',
                'category'          =>  3,
                'penalty_first'     => 'warning:written',
                'penalty_second'    => 'percentage:10',
                'penalty_third'     => 'percentage:25',
                'penalty_fourth'    => 'percentage:50',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'other',
                'duration_unit'     => null,
                'duration_from'     => null,
                'duration_to'       => null,
            ],
            [
                'description'       => 'رفض التفتيش الإداري عند الانصراف.',
                'category'          =>  3,
                'penalty_first'     => 'percentage:25',
                'penalty_second'    => 'percentage:50',
                'penalty_third'     => 'days:1',
                'penalty_fourth'    => 'days:2',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'other',
                'duration_unit'     => null,
                'duration_from'     => null,
                'duration_to'       => null,
            ],
            [
                'description'       => 'عدم تسليم النقود المحصلة لحساب الشركة في المواعيد المحددة دون تبرير مقبول.',
                'category'          =>  3,
                'penalty_first'     => 'days:2',
                'penalty_second'    => 'days:3',
                'penalty_third'     => 'days:5',
                'penalty_fourth'    => 'termination:with_benefit',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'other',
                'duration_unit'     => null,
                'duration_from'     => null,
                'duration_to'       => null,
            ],
            [
                'description'       => 'فصل مع المكافأة الامتناع عن ارتداء الملابس، والأجهزة المقررة للوقاية والسلامة.',
                'category'          =>  3,
                'penalty_first'     => 'warning:written',
                'penalty_second'    => 'days:1',
                'penalty_third'     => 'days:2',
                'penalty_fourth'    => 'days:5',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'other',
                'duration_unit'     => null,
                'duration_from'     => null,
                'duration_to'       => null,
            ],
            [
                'description'       => 'تعمد الخلوة مع الجنس الآخر في أماكن العمل.',
                'category'          =>  3,
                'penalty_first'     => 'days:2',
                'penalty_second'    => 'days:3',
                'penalty_third'     => 'days:5',
                'penalty_fourth'    => 'termination:with_benefit',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'other',
                'duration_unit'     => null,
                'duration_from'     => null,
                'duration_to'       => null,
            ],
            [
                'description'       => 'الإيحاء للآخرين بما يخدش الحياء قولا، أو فعلا.',
                'category'          =>  3,
                'penalty_first'     => 'days:2',
                'penalty_second'    => 'days:3',
                'penalty_third'     => 'days:5',
                'penalty_fourth'    => 'termination:with_benefit',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'other',
                'duration_unit'     => null,
                'duration_from'     => null,
                'duration_to'       => null,
            ],
            [
                'description'       => 'الاعتداء على زملاء العمل بالقول، أو الإشارة، أو باستعمال وسائل الاتصال الالكترونية بالشتم، أو التحقير.',
                'category'          =>  3,
                'penalty_first'     => 'days:2',
                'penalty_second'    => 'days:3',
                'penalty_third'     => 'days:5',
                'penalty_fourth'    => 'termination:with_benefit',
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'other',
                'duration_unit'     => null,
                'duration_from'     => null,
                'duration_to'       => null,
            ],
            [
                'description'       => 'الاعتداء بالإيذاء الجسدي على زملاء العمل، أو على غيرهم بطريقة إباحية.',
                'category'          =>  3,
                'penalty_first'     => 'termination:without_benefit',
                'penalty_second'    => null,
                'penalty_third'     => null,
                'penalty_fourth'    => null,
                'extra_deduction'   => null,
                'user_id'           => 1,

                'violation_type'    => 'other',
                'duration_unit'     =>  null,
                'duration_from'     =>  null,
                'duration_to'       =>  null,
            ],
            [
                'description'       => 'الاعتداء الجسدي، أو القولي، أو بأي وسيلة من وسائل الاتصال الالكترونية على الشركة، أو المدير المسؤول، أو أحد الرؤساء أثناء العمل، أو بسببه.',
                'category'          =>  3,
                'penalty_first'     => 'termination:without_benefit',
                'penalty_second'    =>  null,
                'penalty_third'     =>  null,
                'penalty_fourth'    =>  null,
                'extra_deduction'   =>  null,
                'user_id'           =>  1,

                'violation_type'    => 'other',
                'duration_unit'     =>  null,
                'duration_from'     =>  null,
                'duration_to'       =>  null,
            ],
            [
                'description'       => 'تقديم بلاغ، أو شكوى كيدية.',
                'category'          =>  3,
                'penalty_first'     => 'days:3',
                'penalty_second'    => 'days:5',
                'penalty_third'     => 'termination:with_benefit',
                'penalty_fourth'    =>  null,
                'extra_deduction'   =>  null,
                'user_id'           =>  1,

                'violation_type'    => 'other',
                'duration_unit'     =>  null,
                'duration_from'     =>  null,
                'duration_to'       =>  null,
            ],
            [
                'description'       => 'عدم الامتثال لطلب لجنة التحقيق بالحضور، أو الإدلاء بالأقوال، أو الشهادة.',
                'category'          =>  3,
                'penalty_first'     => 'days:2',
                'penalty_second'    => 'days:3',
                'penalty_third'     => 'days:5',
                'penalty_fourth'    => 'termination:with_benefit',
                'extra_deduction'   =>  null,
                'user_id'           =>  1,

                'violation_type'    => 'other',
                'duration_unit'     =>  null,
                'duration_from'     =>  null,
                'duration_to'       =>  null,
            ],
            [
                'description'       => 'عدم التقيد بالزي الرسمي المعتمد بالمنشأة.',
                'category'          =>  3,
                'penalty_first'     => 'days:1',
                'penalty_second'    => 'days:2',
                'penalty_third'     => 'days:3',
                'penalty_fourth'    => 'days:5',
                'extra_deduction'   =>  null,
                'user_id'           => 1,

                'violation_type'    => 'other',
                'duration_unit'     =>  null,
                'duration_from'     =>  null,
                'duration_to'       =>  null,
            ],
        ];

        // إدخال كل سجل إلى قاعدة البيانات
        foreach ($violations as $violation) {
            SettingsViolation::create([
                'settings_violation_category_id'    => $violation['category'],
                'description'                       => $violation['description'],
                'penalty_first'                     => $violation['penalty_first'],
                'penalty_second'                    => $violation['penalty_second'],
                'penalty_third'                     => $violation['penalty_third'],
                'penalty_fourth'                    => $violation['penalty_fourth'],
                'extra_deduction'                   => $violation['extra_deduction'],
                'user_id'                           => $violation['user_id'],

                'violation_type'                    => $violation['violation_type'],
                'duration_unit'                     => $violation['duration_unit'],
                'duration_from'                     => $violation['duration_from'],
                'duration_to'                       => $violation['duration_to']
            ]);
        }
    }
}
