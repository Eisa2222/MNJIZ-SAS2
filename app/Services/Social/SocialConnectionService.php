<?php

namespace App\Services\Social;

use App\Models\general_setting\SettingsSocial;
use App\Services\Social\LinkedIn\LinkedInService;
use App\Services\Social\Twitter\TwitterService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SocialConnectionService
{
    public function __construct(
        private LinkedInService $linkedInService,
        private TwitterService $twitterService
    ) {}

    public function getAllConnectionsStatus(): array
    {
        $settings = SettingsSocial::orderBy('id', 'asc')->get();

        return [
            'settings' => $settings,
            'linkedin' => $this->getLinkedInStatus($settings),
            'x'        => $this->getTwitterStatus($settings),
        ];
    }

    // الحصول على حالة اتصال LinkedIn
    public function getLinkedInStatus($settings = null): array
    {
        if (!$settings) {
            $settings = SettingsSocial::orderBy('id', 'asc')->get();
        }

        $linkedInSetting = $settings->where('name', 'linkedin')->first();

        $result = [
            'setting'       => $linkedInSetting,
            'connection'    => null,
            'status'        => 'disconnected',
            'error'         => null
        ];

        if (!$linkedInSetting) {
            $result['error'] = 'إعدادات LinkedIn غير موجودة';
            return $result;
        }

        if (!$linkedInSetting->access_token) {
            $result['status']   = 'not_configured';
            $result['error']    = 'رمز الوصول غير مكون';
            return $result;
        }

        try {
            $connectionResult = $this->linkedInService->testConnection($linkedInSetting->access_token);

            if ($connectionResult) {
                $result['connection'] = $connectionResult;
                $result['status'] = 'connected';
            } else {
                $result['status'] = 'error';
                $result['error'] = 'فشل في الحصول على معلومات المستخدم';
            }
        } catch (\Exception $e) {
            $result['status'] = 'error';
            $result['error'] = 'خطأ في الاتصال: ' . $e->getMessage();
        }

        return $result;
    }

    // الحصول على حالة اتصال Twitter
    public function getTwitterStatus($settings = null): array
    {
        if (!$settings) {
            $settings = SettingsSocial::orderBy('id', 'asc')->get();
        }

        $twitterSetting = $settings->where('name', 'x')->first();

        $result = [
            'setting'       => $twitterSetting,
            'connection'    => null,
            'status'        => 'disconnected',
            'error'         => null,
        ];

        if (!$twitterSetting) {
            $result['error'] = 'إعدادات Twitter غير موجودة';
            return $result;
        }

        if (!$this->hasAllTwitterCredentials($twitterSetting)) {
            $result['status']   = 'not_configured';
            $result['error']    = 'بيانات الاعتماد غير مكتملة';
            return $result;
        }

        try {

            // اختبار الاتصال
            $connectionResult = $this->twitterService->testConnection(
                $twitterSetting->api_key,
                $twitterSetting->api_secret,
                $twitterSetting->access_token,
                $twitterSetting->access_token_secret
            );

            if ($connectionResult['success']) {
                $result['connection'] = $connectionResult;
                $result['status'] = 'connected';
            } else {
                $result['status'] = 'error';
                $result['error'] = $connectionResult['message'] ?? 'فشل في الاتصال';
            }
        } catch (\Exception $e) {
            $result['status'] = 'error';
            $result['error'] = 'خطأ في الاتصال: ' . $e->getMessage();
        }

        return $result;
    }


    // تحدد نوع المنصة لاختبار الاتصال
    public function testSpecificConnection(string $platform, array $credentials): array
    {
        try {
            switch ($platform) {
                case 'linkedin':
                    return $this->testLinkedInConnection($credentials);

                case 'x':
                    return $this->testTwitterConnection($credentials);

                default:
                    return [
                        'success' => false,
                        'message' => 'منصة غير مدعومة: ' . $platform
                    ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'خطأ في اختبار الاتصال: ' . $e->getMessage()
            ];
        }
    }


    public function clearServiceCache(string $serviceName): void
    {
        switch ($serviceName) {
            case 'linkedin':
                Cache::forget('linkedin_user_info');
                Cache::forget('linkedin_access_token');
                break;

            case 'x':
                Cache::forget('x_twitter_core');
                break;
        }
    }

    /*
    |============================================================================
    |============================================================================
    |                          private functions
    |============================================================================
    |============================================================================
    */
    // test LinkedIn connection
    private function testLinkedInConnection(array $credentials): array
    {
        if (empty($credentials['access_token'])) {
            return [
                'success' => false,
                'message' => 'رمز الوصول مطلوب'
            ];
        }

        return $this->linkedInService->testConnection($credentials['access_token']);
    }

    // test Twitter connection
    private function testTwitterConnection(array $credentials): array
    {
        foreach (['api_key', 'api_secret', 'access_token', 'access_token_secret'] as $field) {
            if (empty($credentials[$field])) {
                return [
                    'success' => false,
                    'message' => 'كل الحقول مطلوبة'
                ];
            }
        }

        return $this->twitterService->testConnection(
            $credentials['api_key'],
            $credentials['api_secret'],
            $credentials['access_token'],
            $credentials['access_token_secret']
        );
    }

    private function hasAllTwitterCredentials($twitterSetting): bool
    {
        if (!$twitterSetting) {
            return false;
        }

        return !empty($twitterSetting->api_key) &&
            !empty($twitterSetting->api_secret) &&
            !empty($twitterSetting->access_token) &&
            !empty($twitterSetting->access_token_secret);
    }
}
