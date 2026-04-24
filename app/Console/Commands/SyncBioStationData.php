<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BioStationService;
use App\Models\Setting;
use App\Models\GeneralSetting\SystemSetting\Settings;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SyncBioStationData extends Command
{

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
        
        $settings = Settings::current();

        if (! $settings->exists || ! $settings->biostation_api_key) {
            Log::warning('BioStation settings are not configured.');
            $this->error('BioStation settings are not configured.');
            return 1;
        }

        $this->info('Starting synchronization with BioStation...');

        // استرجاع البصمات من الجهاز
        $fingerprints = $this->bioStationService->getFingerprintsFromDevice();

        if ($fingerprints === null) {
            $this->error('Failed to fetch fingerprints from BioStation.');
            return 1;
        }

        // مثال: تحديث البصمات في النظام بناءً على البيانات المسترجعة
        foreach ($fingerprints as $fingerprintData) {
            // افترض أن البيانات تحتوي على 'finger_id' و 'template' و 'user_id'
            $fingerprint = \App\Models\Fingerprint::updateOrCreate(
                ['finger_id' => $fingerprintData['finger_id']],
                ['template' => $fingerprintData['template'], 'user_id' => $fingerprintData['user_id']]
            );

            $this->info("Fingerprint {$fingerprint->finger_id} synchronized.");
        }

        // تحديث وقت التزامن الأخير
        $settings->biostation_last_sync = Carbon::now();
        $settings->save();

        $this->info('Synchronization completed successfully.');

        return 0;
    }
}
