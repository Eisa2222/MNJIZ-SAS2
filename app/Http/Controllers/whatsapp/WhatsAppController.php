<?php

namespace App\Http\Controllers\whatsapp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\OperationsCenter\Customer\Customers;
use App\Models\MessageLog;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Helpers\WhatsAppHelper;

class WhatsAppController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Send Message
    |--------------------------------------------------------------------------
    */
    public function sendMassMessage(Request $request)
    {
        if (!WhatsAppHelper::isEnabled()) {
            return response()->json(['error' => 'خدمة الواتساب معطلة. يرجى تفعيلها من إعدادات النظام.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'customer_ids' => 'required|array',
            'customer_ids.*' => 'integer|exists:customers,id',
        ], [
            'customer_ids.required' => 'يجب تحديد عملاء لإرسال الرسالة.',
            'customer_ids.array' => 'صيغة معرفات العملاء غير صحيحة.',
            'customer_ids.*.exists' => 'العميل المحدد غير موجود.',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $customerIds = $request->input('customer_ids');
        $contentSid = "HX78fc32efa36ad90ef5ac15bd9190a364";
        $customers = Customers::whereIn('id', $customerIds)->get();
        $failedNumbers = [];
        $successfulRecipients = [];

        foreach ($customers as $customer) {
            if ($customer->contact_number) {
                $variables = [
                    "1" => $customer->name,
                    "2" => $request->session_name ?? 'غير محدد',
                    "3" => $request->summary_report ?? 'غير محدد',
                    "4" => $request->notes ?? 'لا توجد ملاحظات',
                    "5" => $request->relationship_manager_name ?? 'غير محدد',
                    "6" => $request->relationship_manager_mobile ?? 'غير متوفر',
                    "7" => $request->relationship_manager_email ?? 'غير متوفر',
                ];

                if ($request->media_url) {
                    $variables["media_filename"] = basename($request->media_url);
                }

                $result = WhatsAppHelper::sendMessage(
                    $customer->contact_number,
                    $contentSid,
                    $variables
                );

                if ($result['success']) {
                    $successfulRecipients[] = [
                        'type' => 'customer',
                        'id' => $customer->id,
                    ];
                } else {
                    $failedNumbers[] = $customer->contact_number;
                    Log::error('Error sending WhatsApp message to ' . $customer->contact_number . ': ' . $result['error']);
                }
            } else {
                Log::warning('Customer with ID ' . $customer->id . ' has no contact_number. Message not sent.');
            }
        }

        if (!empty($successfulRecipients)) {
            MessageLog::create([
                'sender_id' => Auth::id(),
                'message_text' => 'تم إرسال رسالة واتساب ',
                'platform' => 'WhatsApp',
                'recipients' => $successfulRecipients,
            ]);
        }

        if (empty($failedNumbers)) {
            return response()->json(['success' => 'تم إرسال رسائل الواتساب بنجاح.']);
        } else {
            Log::error('Some WhatsApp messages failed to send: ' . implode(', ', $failedNumbers));
            return response()->json([
                'warning' => 'تم إرسال بعض الرسائل بنجاح، لكن فشل إرسال البعض الآخر.',
                'failed_numbers' => $failedNumbers
            ], 207);
        }
    }
}
