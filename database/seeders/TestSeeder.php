<?php

namespace Database\Seeders;

use App\Models\OperationsCenter\Customer\Customers;
use App\Models\Hr\Employees\Employees;
use App\Models\OperationsCenter\Offer\Offers;
use App\Models\judicial_affairs\Opponent;
use App\Models\judicial_affairs\PowerOfAttorney;
use App\Models\judicial_affairs\Project;
use App\Models\judicial_affairs\Session;
use App\Models\judicial_affairs\SessionComment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Employees::factory()->count(10)->create();
        Customers::factory()->count(10)->create();
        // Offers::factory()->count(10)->create();
        // Contract::factory()->count(10)->create();
        // Opponent::factory()->count(10)->create();
        // PowerOfAttorney::factory()->count(50)->create();



        // Project::factory()->count(10)->create();
        // Lawsuit::factory()->count(10)->create();
        // Session::factory()->count(10)->create();
        // SessionComment::factory()->count(10)->create();

    }
}
