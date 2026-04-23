<?php

namespace App\Console\Commands;

use App\Models\LegalAffair\PowerOfAttorney\PowerOfAttorney;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateStatusPowerAttorney extends Command
{
    protected $signature = 'powerattorneys:update-expired';
    protected $description = 'تحديث حالة الوكالات إلى منتهية عند مرور تاريخ الانتهاء';

    public function handle()
    {
        $today = Carbon::today()->toDateString();

        $affected = PowerOfAttorney::where('date_expiry', '<=', $today)
            ->where('status', '!=', 'expired')
            ->update(['status' => 'expired']);

        $this->info("تم تحديث {$affected} وكالة(ات) إلى حالة منتهية.");
        return 0;
    }
}