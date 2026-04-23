<?php

namespace Database\Factories;

use App\Models\Hr\Employees\Employees;
use App\Models\judicial_affairs\Session;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AssignedSession>
 */
class AssignedSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'session_id' => Session::factory(), // إنشاء جلسة إذا لم تكن موجودة
            'assigned_to' => Employees::factory(), // إنشاء موظف إذا لم يكن موجودًا
            'user_id' => User::factory(), // إنشاء مستخدم إذا لم يكن موجودًا
        ];
    }
}
