<?php

namespace App\Services;

use App\Models\GeneralSetting\SystemSetting\Settings;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class BioStationService
{
    protected $client;
    protected $apiKey;
    protected $apiUrl;
    protected $deviceIp;
    protected $devicePort;
    protected $timezone;
    protected $deviceName;
    protected $syncInterval;
    protected $lastSync;

    public function __construct()
    {
        // التأكد من وجود جدول settings قبل الاستعلام عليه
        if (!Schema::hasTable('settings')) {
            return 1;
        }


        $settings = Settings::first();

        $this->apiKey         = $settings->biostation_api_key ?? null;
        $this->apiUrl         = $settings->biostation_api_url ?? 'https://api.biostation.com';
        $this->deviceIp       = $settings->biostation_device_ip ?? '127.0.0.1';
        $this->devicePort     = $settings->biostation_device_port ?? 80;
        $this->timezone       = $settings->biostation_timezone ?? 'UTC';
        $this->deviceName     = $settings->biostation_device_name ?? 'Default Device';
        $this->syncInterval   = $settings->biostation_sync_interval ?? 5;
        $this->lastSync       = $settings->biostation_last_sync ?? null;

        if ($this->apiKey) {
            $this->client = new Client([
                'base_uri' => $this->apiUrl,
                'timeout'  => 10.0,
            ]);
        }
    }

    /**
     * التحقق مما إذا كانت إعدادات BioStation مُكوّنة
     */
    public function isConfigured()
    {
        return $this->apiKey && $this->apiUrl && $this->deviceIp && $this->devicePort;
    }

    /**
     * التقاط بصمة من جهاز BioStation
     *
     * @return array
     */
    // public function captureFingerprint()
    // {
    //     if (!$this->isConfigured()) {
    //         Log::warning('BioStation settings are not properly configured.');
    //         return ['success' => false, 'message' => 'BioStation settings are not configured.'];
    //     }

    //     try {
    //         // افترض أن جهاز BioStation يقدم Endpoint لالتقاط البصمة
    //         $response = $this->client->request('POST', '/capture', [
    //             'headers' => [
    //                 'Authorization' => 'Bearer ' . $this->apiKey,
    //                 'Accept'        => 'application/json',
    //             ],
    //             // يمكنك إضافة بيانات إضافية إذا كانت مطلوبة
    //         ]);

    //         if ($response->getStatusCode() == 200) {
    //             $data = json_decode($response->getBody(), true);

    //             // تحقق من البيانات المستلمة
    //             if (isset($data['finger_id']) && isset($data['template'])) {
    //                 return ['success' => true, 'finger_id' => $data['finger_id'], 'template' => $data['template']];
    //             } else {
    //                 Log::error('Incomplete fingerprint data received from BioStation.');
    //                 return ['success' => false, 'message' => 'Incomplete fingerprint data received.'];
    //             }
    //         } else {
    //             Log::error('Failed to capture fingerprint: ' . $response->getBody());
    //             return ['success' => false, 'message' => 'Failed to capture fingerprint.'];
    //         }
    //     } catch (\Exception $e) {
    //         Log::error('Error capturing fingerprint: ' . $e->getMessage());
    //         return ['success' => false, 'message' => 'Error capturing fingerprint.'];
    //     }
    // }

    public function captureFingerprint()
    {
        if (!$this->isConfigured()) {
            Log::warning('BioStation settings are not properly configured.');
            return ['success' => false, 'message' => 'BioStation settings are not configured.'];
        }

        try {
            // افترض أن جهاز BioStation يقدم Endpoint لالتقاط البصمة
            $response = $this->client->request('POST', '/capture', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept'        => 'application/json',
                ],
                'verify' => false, // تعطيل التحقق من الشهادة
                // يمكنك إضافة بيانات إضافية إذا كانت مطلوبة
            ]);

            if ($response->getStatusCode() == 200) {
                $data = json_decode($response->getBody(), true);

                // تحقق من البيانات المستلمة
                if (isset($data['finger_id']) && isset($data['template'])) {
                    return ['success' => true, 'finger_id' => $data['finger_id'], 'template' => $data['template']];
                } else {
                    Log::error('Incomplete fingerprint data received from BioStation.');
                    return ['success' => false, 'message' => 'Incomplete fingerprint data received.'];
                }
            } else {
                Log::error('Failed to capture fingerprint: ' . $response->getBody());
                return ['success' => false, 'message' => 'Failed to capture fingerprint.'];
            }
        } catch (\Exception $e) {
            Log::error('Error capturing fingerprint: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error capturing fingerprint.'];
        }
    }


    /**
     * إضافة بصمة إلى جهاز BioStation
     *
     * @param string $fingerId
     * @param string $template
     * @return bool
     */
    public function addFingerprintToDevice($fingerId, $template)
    {
        if (!$this->apiKey) {
            Log::warning('BioStation API Key is not set.');
            return false;
        }

        try {
            $response = $this->client->request('POST', '/fingerprints', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept'        => 'application/json',
                ],
                'json' => [
                    'finger_id'   => $fingerId,
                    'template'    => $template,
                    'timezone'    => $this->timezone,
                    'device_name' => $this->deviceName,
                ],
            ]);

            if ($response->getStatusCode() == 201) {
                return true;
            } else {
                Log::error('Failed to add fingerprint to BioStation: ' . $response->getBody());
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Error adding fingerprint to BioStation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * إزالة بصمة من جهاز BioStation
     *
     * @param string $fingerId
     * @return bool
     */
    public function removeFingerprintFromDevice($fingerId)
    {
        if (!$this->apiKey) {
            Log::warning('BioStation API Key is not set.');
            return false;
        }

        try {
            $response = $this->client->request('DELETE', "/fingerprints/{$fingerId}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept'        => 'application/json',
                ],
            ]);

            if ($response->getStatusCode() == 200) {
                return true;
            } else {
                Log::error('Failed to remove fingerprint from BioStation: ' . $response->getBody());
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Error removing fingerprint from BioStation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * استرجاع بصمات من جهاز BioStation
     *
     * @return array|null
     */
    public function getFingerprintsFromDevice()
    {
        if (!$this->apiKey) {
            Log::warning('BioStation API Key is not set.');
            return null;
        }

        try {
            $response = $this->client->request('GET', '/fingerprints', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept'        => 'application/json',
                ],
            ]);

            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error('Error fetching fingerprints from BioStation: ' . $e->getMessage());
            return null;
        }
    }

    // يمكنك إضافة دوال أخرى حسب احتياجاتك
}
