<?php

namespace Database\Factories\judicial_affairs;

use App\Models\OperationsCenter\Customer\Customers;
use App\Models\Hr\Employees\Employees;
use App\Models\judicial_affairs\PowerOfAttorney;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Generator as Faker;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\judicial_affairs\PowerOfAttorney>
 */
class PowerOfAttorneyFactory extends Factory
{
    protected $model = PowerOfAttorney::class;

    public function definition()
    {
        // تعيين لغة Faker إلى العربية
        $this->faker = \Faker\Factory::create('ar_SA');

        // الحصول على قائمة معرفات من الجداول المرتبطة
        $customerIds = Customers::pluck('id')->toArray();
        $attorneyIds = Employees::pluck('id')->toArray();

        // توليد رقم توكيل فريد
        $powerNumber = $this->faker->unique()->regexify('\d{6}');

        // توليد تاريخ الإصدار بين عامين مضت والآن
        $dateIssued = $this->faker->dateTimeBetween('-2 years', 'now');

        // توليد تاريخ الانتهاء بين تاريخ الإصدار والعامين القادمين
        $dateExpiry = $this->faker->dateTimeBetween($dateIssued, '+2 years');

        // تحديد حالة الوكالة بناءً على تاريخ الانتهاء
        if (Carbon::instance($dateExpiry)->isPast()) {
            $status = 'expired';
        } else {
            $status = $this->faker->randomElement(['active', 'revoked']);
        }

        // توليد مرفقات ملف وهمية
        $fileAttachment = $this->faker->optional()->imageUrl();

        // توليد نطاق التوكيل
        $scopes = [
            'تمثيل قانوني في المحاكم',
            'إدارة العقود والاتفاقيات',
            'التفاوض على الصفقات التجارية',
            'الاستشارات القانونية المتخصصة',
            'حل النزاعات القانونية',
            'التحكيم والوساطة',
            'إعداد التقارير القانونية',
            'تقديم المشورة في القضايا الجنائية',
            'حماية الملكية الفكرية',
            'الامتثال والتنظيمات القانونية',
        ];

        // توليد ملاحظات
        $notesList = [
            'توكيل صادر بموجب القانون السعودي.',
            'لا توجد ملاحظات إضافية.',
            'تم تجديد التوكيل مؤخرًا.',
            'التوكيل صالح حتى نهاية السنة الحالية.',
            'تم إلغاء التوكيل بناءً على طلب العميل.',
            'التوكيل يشمل جميع الأمور القانونية المتعلقة بالشركة.',
            'توكيل محدود بالتحكيم فقط.',
            'تم تمديد صلاحية التوكيل لمدة عام إضافي.',
            'التوكيل يغطي قضايا الملكية الفكرية فقط.',
            'التوكيل شامل لجميع الأنشطة القانونية للشركة.',
        ];

        return [
            'power_name' => 'توكيل ' . $this->faker->randomElement(['شامل', 'محدد', 'خاص', 'مؤقت', 'دائم']),
            'power_number' => $powerNumber,
            // إذا كانت العلاقات Many-to-Many، يمكنك ترك 'customer_id' و 'attorney_id' خارج المصنع
            'scope' => $this->faker->optional()->randomElement($scopes),
            'date_issued' => $dateIssued->format('Y-m-d'),
            'date_expiry' => $dateExpiry->format('Y-m-d'),
            'status' => $status,
            'notes' => $this->faker->optional()->randomElement($notesList),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (PowerOfAttorney $powerOfAttorney) {
            // جلب العملاء بشكل عشوائي وربطهم بالتوكيل
            $customers = Customers::inRandomOrder()->take(rand(1, 3))->pluck('id');
            $powerOfAttorney->customers()->attach($customers->all(), ['user_id' => 1]);

            // جلب الوكلاء بشكل عشوائي وربطهم بالتوكيل
            $agents = Employees::where('job_title', 'محامي')->inRandomOrder()->take(rand(1, 3))->pluck('id');
            $powerOfAttorney->agents()->attach($agents->all(), ['user_id' => 1]);
        });
    }
}
