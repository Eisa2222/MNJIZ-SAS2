<?php

namespace Database\Factories\business_development;

use App\Models\OperationsCenter\Customer\Customers;
use App\Models\general_setting\SettingsClientStatus;
use App\Models\general_setting\SettingsCountry;
use App\Models\general_setting\SettingsMarketingChannel;
use App\Models\general_setting\SettingsSector;
use App\Models\Hr\Employees\Employees;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\business_development\Customer>
 */
class CustomersFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Customers::class;

    public function definition(): array
    {
        $userIds = User::pluck('id')->toArray();
        $countryIds = SettingsCountry::pluck('id')->toArray();
        $statusIds = SettingsClientStatus::pluck('id')->toArray();
        $employeeIds = Employees::pluck('id')->toArray();
        $marketingChannelIds = SettingsMarketingChannel::pluck('id')->toArray();
        $sectorIds = SettingsSector::pluck('id')->toArray();
        $commonName = $this->faker->name();

        return [
            'user_id' => $this->faker->randomElement($userIds),
            'name' => $commonName,
            'title' => $commonName,
            'nationality' => $this->faker->randomElement($countryIds),
            'status' => $this->faker->randomElement($statusIds),
            'contact_number' => $this->faker->numerify('05########'),
            'email' => $this->faker->unique()->safeEmail(),
            'address' => $this->faker->address(),
            'relationshipManager' => $this->faker->randomElement($employeeIds),
            'marketingChannel' => $this->faker->randomElement($marketingChannelIds),
            'detailedMarketingChannel' => $this->faker->randomElement($employeeIds),
            'sector' => $this->faker->randomElement($sectorIds),
        ];
    }
}
