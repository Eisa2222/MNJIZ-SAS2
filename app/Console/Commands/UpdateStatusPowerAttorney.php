<?php

namespace App\Console\Commands;

use App\Console\Concerns\IteratesTenants;
use App\Models\LegalAffair\PowerOfAttorney\PowerOfAttorney;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateStatusPowerAttorney extends Command
{
    use IteratesTenants;

    protected $signature   = 'powerattorneys:update-expired';
    protected $description = 'تحديث حالة الوكالات إلى منتهية عند مرور تاريخ الانتهاء (per tenant)';

    public function handle(): int
    {
        $today = Carbon::today()->toDateString();

        $this->perTenant(function (Tenant $tenant) use ($today) {
            // TenantScope auto-appends WHERE tenant_id = current tenant.
            $affected = PowerOfAttorney::where('date_expiry', '<=', $today)
                ->where('status', '!=', 'expired')
                ->update(['status' => 'expired']);

            if ($affected > 0) {
                $this->info("[tenant:{$tenant->slug}] تم تحديث {$affected} وكالة(ات) إلى حالة منتهية.");
            }
        });

        return self::SUCCESS;
    }
}
