<?php

namespace App\Console\Commands;

use App\Console\Concerns\IteratesTenants;
use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Models\Tenant;
use App\Services\BioStationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncBioStationData extends Command
{
    use IteratesTenants;

    private $bioStationService;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'biostation:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync data with BioStation device';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(BioStationService $bioStationService)
    {
        parent::__construct();
        $this->bioStationService = $bioStationService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->perTenant(function (Tenant $tenant) {
            $settings = Settings::current();

            if (! $settings->exists || ! $settings->biostation_api_key) {
                return;   // not configured for this tenant, skip quietly
            }

            $this->info("[tenant:{$tenant->slug}] Starting BioStation sync...");

            $fingerprints = $this->bioStationService->getFingerprintsFromDevice();

            if ($fingerprints === null) {
                $this->error("[tenant:{$tenant->slug}] Failed to fetch fingerprints.");
                return;
            }

            foreach ($fingerprints as $fingerprintData) {
                // Fingerprint is tenant-scoped; creating hook auto-fills tenant_id.
                \App\Models\Fingerprint::updateOrCreate(
                    ['finger_id' => $fingerprintData['finger_id']],
                    ['template' => $fingerprintData['template'], 'user_id' => $fingerprintData['user_id']]
                );
            }

            $settings->biostation_last_sync = Carbon::now();
            $settings->save();

            $this->info("[tenant:{$tenant->slug}] Sync completed.");
        });

        return 0;
    }
}
