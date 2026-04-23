<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemAdministration\CredentialsManagement\ApprovalFlow;


class ApprovalFlowsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['offer', 'contract', 'leave', 'wps', 'clearance_certificate', 'advance', 'reward', 'deduction', 'content','custody'] as $type) {
            ApprovalFlow::firstOrCreate(['type' => $type]);
        }
    }
}
