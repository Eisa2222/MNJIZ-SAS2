<?php

namespace App\Services\SMS;

use App\Helpers\General;

class SmsService
{
    public function sendSmsToNumber(string $message, string $phoneNumber): array
    {
        $formattedNumber = ltrim($phoneNumber, '0');
        $sent = General::sendSMS($message, $formattedNumber);


        if ($sent) {
            $result = [
                'success'         => true,
                'message'         => 'تم إرسال الرسالة بنجاح',
            ];
        } else {
            $result = [
                'success'         => false,
                'message'         => 'فشل في إرسال الرسالة',
            ];
        }
        return $result;
    }
}