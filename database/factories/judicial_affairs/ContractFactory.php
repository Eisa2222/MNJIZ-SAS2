<?php

namespace Database\Factories\judicial_affairs;

use App\Models\OperationsCenter\Customer\Customers;
use App\Models\general_setting\SettingsContractStatus;
use App\Models\Hr\Employees\Employees;
use App\Models\OperationsCenter\Contract\Contract;
use App\Models\OperationsCenter\Offer\Offers;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\judicial_affairs\Contract>
 */
class ContractFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Contract::class;

    public function definition()
    {
        // جلب جميع معرفات العروض
        $OfferIds = Offers::whereNotNull('relationship_manager_id')
            ->whereNotNull('customer_id')
            ->pluck('id')->toArray();

        // التأكد من أن هناك عروض متاحة
        if (empty($OfferIds)) {
            throw new Exception('No offers with valid relationship_manager_id and customer_id found.');
        }

        // جلب عرض عشوائي بناءً على المعرفات المتاحة
        $offer = Offers::find($this->faker->randomElement($OfferIds));

        // التأكد من أن العرض تم جلبه بنجاح
        if ($offer) {
            $relationship_manager_id = $offer->relationship_manager_id;
            $customer_id = $offer->customer_id;
        } else {
            throw new Exception('Offer not found.');
        }


        // الحصول على قائمة معرفات من الجداول المرتبطة
        $offerIds = Offers::pluck('id')->toArray();
        $employeeIds = Employees::pluck('id')->toArray();
        $contractStatusIds = SettingsContractStatus::pluck('id')->toArray();

        // توليد رقم عقد فريد بدون شرطات
        $contractNumber = $this->faker->unique()->numerify('C-####-####');

        return [
            'contract_name' => 'عقد ' . $this->faker->randomElement([
                'تمثيل قانوني',
                'خدمات استشارية',
                'إعداد عقود',
                'الدفاع الجنائي',
                'التحكيم والوساطة'
            ]),
            'contract_number' => $contractNumber, // رقم العقد الفريد
            'customer_id' => $customer_id,
            'expected_closure_date' => $this->faker->date(),
            'contract_start_date' => $this->faker->date(),
            'contract_end_date' => $this->faker->optional()->date(),
            'offer_id' => $offer->id,
            'contract_manager_id' => $this->faker->randomElement($employeeIds),
            'relationship_manager_id' => $relationship_manager_id,
            'contract_status_id' => $this->faker->randomElement($contractStatusIds),
            'created_by' => $this->faker->randomElement(Employees::pluck('id')->toArray()),
            'updated_by' => $this->faker->randomElement(Employees::pluck('id')->toArray()),

        ];
    }
}
