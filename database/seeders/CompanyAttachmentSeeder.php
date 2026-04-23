<?php

namespace Database\Seeders;

use App\Models\GeneralSetting\SystemSetting\CompanyAttachment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompanyAttachmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CompanyAttachment::create([]);
    }
}
