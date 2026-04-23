<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert(
            [
                'name ' => 'lawyer',
                'guard_name ' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name ' => 'hr',
                'guard_name ' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name ' => 'business development',
                'guard_name ' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name ' => 'legal affairs',
                'guard_name ' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }
}
