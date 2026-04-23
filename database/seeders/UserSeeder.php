<?php

namespace Database\Seeders;

use App\Models\Hr\Employees\Employees;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User; // تأكد من استيراد نموذج المستخدم

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء المستخدم باستخدام نموذج Eloquent
        $user = User::create([
            'name' => 'مدير النظام',
            'email' => 'admin@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('123456789'), // كلمة المرور المشفرة
            'phone' => '0123456789',
            'status' => 'active',
            'nationality' => 1,
            'job' => 'Administrator',
            'must_change_password' => 0,
            'image' => '', // تأكد من وجود الصورة في مجلد التخزين المناسب
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Employees::create([
            'name' => 'مدير النظام',
            'id_number' => 1,
            'user_id' => 1,
            'work_email' => 'admin@gmail.com',

            'created_at' => now(),
            'updated_at' => now(),
        ]);
        // تعيين دور "Admin" للمستخدم
        $user->assignRole('Admin');
    }
}
