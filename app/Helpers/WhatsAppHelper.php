<?php

namespace App\Helpers;

use App\Models\GeneralSetting\SystemSetting\Settings;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class WhatsAppHelper
{

    public static function isEnabled()
    {
        $settings = Settings::first();

        if (!$settings || !$settings->whatsapp_enabled) {
            return false;
        }



        return true;
    }

    public static function getCredentials()
    {
        $settings = Settings::first();

        if (!$settings) {
            return null;
        }

        return [
            'account_sid' => $settings->twilio_account_sid,
            'auth_token' => $settings->twilio_auth_token,
            'from' => $settings->twilio_whatsapp_from
        ];
    }


    public static function formatPhoneNumber($number)
    {
        $number = preg_replace('/\D/', '', $number);

        if (substr($number, 0, 1) === '0') {
            $number = '+966' . substr($number, 1);
        } elseif (substr($number, 0, 4) !== '+966' && substr($number, 0, 1) !== '+') {
            $number = '+' . $number;
        }

        return "whatsapp:" . $number;
    }


    public static function sendMessage($to, $contentSid, $variables)
    {
        if (!self::isEnabled()) {
            return [
                'success' => false,
                'error' => 'خدمة WhatsApp معطلة'
            ];
        }

        $credentials = self::getCredentials();
        if (!$credentials) {
            return [
                'success' => false,
                'error' => 'لم يتم العثور على بيانات اعتماد WhatsApp'
            ];
        }

        try {
            $twilio = new Client($credentials['account_sid'], $credentials['auth_token']);

            $formattedNumber = self::formatPhoneNumber($to);

            $message = $twilio->messages->create(
                $formattedNumber,
                [
                    "from" => $credentials['from'],
                    "contentSid" => $contentSid,
                    "contentVariables" => json_encode($variables)
                ]
            );
            return [
                'success' => true,
                'message' => $message
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
