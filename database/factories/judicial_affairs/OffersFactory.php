<?php

namespace Database\Factories\judicial_affairs;

use App\Models\OperationsCenter\Customer\Customers;
use App\Models\general_setting\SettingsStagePriceOffer;
use App\Models\Hr\Employees\Employees;
use App\Models\OperationsCenter\Offer\Offers;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\judicial_affairs\Offer>
 */
class OffersFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Offers::class;

    public function definition()
    {
        $offerNames = [
            'عرض الاستشارة القانونية الشاملة',
            'عرض تمثيل قانوني في المحاكم',
            'عرض إعداد وصياغة العقود',
            'عرض خدمات التحكيم والوساطة',
            'عرض تمثيل الشركات والمؤسسات',
            'عرض مراجعة اللوائح الداخلية للشركات',
            'عرض الدفاع الجنائي المتكامل',
            'عرض خدمات التوطين القانوني',
            'عرض إدارة النزاعات القانونية',
            'عرض استشارات الملكية الفكرية',
            'عرض خدمات الهجرة والقوانين الدولية',
            'عرض صياغة الاتفاقيات التجارية',
            'عرض التدقيق القانوني للمشاريع',
            'عرض خدمات الوساطة العمالية',
            'عرض الاستشارات القانونية الإلكترونية',
        ];
        // تعيين لغة Faker إلى العربية

        // الحصول على قائمة معرفات من الجداول المرتبطة
        $stagePriceOfferIds = SettingsStagePriceOffer::pluck('id')->toArray();
        $customerIds = Customers::pluck('id')->toArray();

        $customer = Customers::find($this->faker->randomElement($customerIds));
        $relationship_manager_id = $customer->relationshipManager;

        return [
            'offer_name' => $this->faker->randomElement($offerNames),
            'stage_price_offer_id' => $this->faker->randomElement($stagePriceOfferIds),
            'customer_id' => $customer->id,
            'relationship_manager_id' => $relationship_manager_id,
            'start_date' => $this->faker->date(),
            'cut_off_value' => $this->faker->optional()->numberBetween(1000, 10000),
            'value_based_time' => $this->faker->optional()->numberBetween(500, 5000),
            'percentage_based_work' => $this->faker->optional()->numberBetween(1, 100),
            'created_by' => $this->faker->randomElement(Employees::pluck('id')->toArray()),
            'updated_by' => $this->faker->randomElement(Employees::pluck('id')->toArray()),
        ];
    }
}
