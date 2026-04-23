<?php

namespace App\Http\Controllers\GeneralSetting\SystemSetting;

use App\Helpers\General;
use App\Http\Controllers\Controller;
use App\Http\Requests\GeneralSetting\SystemSetting\companyAttachmentsRequest;
use App\Http\Requests\GeneralSetting\SystemSetting\MainSettingsRequest;
use App\Models\GeneralSetting\SystemSetting\CompanyAttachment;
use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Services\GeneralSetting\SystemSetting\SystemSettingsServices;
use Illuminate\Support\Facades\Cache;

class SystemSettingsController extends Controller
{

    public function __construct(private SystemSettingsServices $service)
    {
        $this->middleware('can:إعدادات النظام')->only(['index', 'update', 'companyAttachments']);
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $settings       = Settings::first();
        $attachments    = CompanyAttachment::first();


        $weekDays = [
            'sunday'    => 'الأحد',
            'monday'    => 'الإثنين',
            'tuesday'   => 'الثلاثاء',
            'wednesday' => 'الأربعاء',
            'thursday'  => 'الخميس',
            'friday'    => 'الجمعة',
            'saturday'  => 'السبت',
        ];

        $weeklyDaysOff = [];
        if ($settings && isset($settings->weekly_days_off)) {
            if (is_string($settings->weekly_days_off)) {
                $weeklyDaysOff = json_decode($settings->weekly_days_off, true) ?: [];
            } else if (is_array($settings->weekly_days_off)) {
                $weeklyDaysOff = $settings->weekly_days_off;
            }
        }

        return view('general_setting.system_settings.index', compact('settings', 'attachments', 'weekDays', 'weeklyDaysOff'));
    }


    public function update(MainSettingsRequest $request)
    {
        // تخزين التبويبة النشطة في الجلسة
        if ($request->has('active_tab')) {
            session(['active_tab' => $request->input('active_tab')]);
        }

        // التحقق من صحة البيانات
        $validated = $request->validated();

        // الحصول على إعدادات النظام الحالية أو إنشاء جديدة إذا لم توجد
        $settings = Settings::first();
        if (!$settings) {
            $settings = new Settings();
        }

        // تحديث إعدادات عامة
        if ($request->has('office_name')) {
            $settings->office_name = $validated['office_name'];
            $settings->archive_delete_duration = $validated['archive_delete_duration'];
            $settings->logo_text = $validated['logo_text'];


            // تحديث وضع الصيانة
            $settings->maintenance_mode = $request->has('maintenance_mode');



            if ($request->hasFile('image')) {
                $settings->image = $request->file('image')->store('image', 'public');
            }
        }

        // تحديث إعدادات الطباعة
        if ($request->has('template_image')) {
            if ($request->hasFile('template_image')) {
                $settings->template_image = $request->file('template_image')->store('print_settings', 'public');
            }
        }


        if ($request->hasFile('signature')) {
            $settings->signature = $request->file('signature')->store('print_settings', 'public');
        }


        // تحديث إعدادات Microsoft
        if ($request->has('microsoft_client_id')) {
            $settings->microsoft_client_id = $validated['microsoft_client_id'];
            $settings->microsoft_client_secret = $validated['microsoft_client_secret'];
            $settings->microsoft_redirect_uri = $validated['microsoft_redirect_uri'];
            $settings->microsoft_tenant_id = $validated['microsoft_tenant_id'];
            $settings->main_email = $validated['main_email'];
        }

        if ($request->has('twilio_account_sid')) {
            $settings->twilio_account_sid = $validated['twilio_account_sid'];
            $settings->twilio_auth_token = $validated['twilio_auth_token'];
            $settings->twilio_whatsapp_from = $validated['twilio_whatsapp_from'];
            $settings->whatsapp_enabled = $request->has('whatsapp_enabled');
        }

        // تحديث إعدادات الرسائل النصية
        if ($request->has('sms_provider')) {
            if (General::test4JawalyCredentials($request->sms_api_key, $request->sms_api_secret, $request->sms_sender_id)) {

                $settings->sms_provider = $validated['sms_provider'];
                $settings->sms_api_key = $validated['sms_api_key'];
                $settings->sms_api_secret = $validated['sms_api_secret'];
                $settings->sms_sender_id = $validated['sms_sender_id'];
                $settings->sms_enabled = $request->has('sms_enabled');
            } else {
                return redirect()->route('general-settings.system-settings.index')->with('error', 'عفوا إعدادات  SMS غير صحيحة ')->withFragment($request->input('active_tab'));
            }
        }

        // اعدادات البصمة
        if ($request->has('biostation_api_key')) {
            $settings->biostation_api_key = $validated['biostation_api_key'] ?? null;
            $settings->biostation_api_url = $validated['biostation_api_url'] ?? 'https://api.biostation.com';
            $settings->biostation_device_ip = $validated['biostation_device_ip'] ?? null;
            $settings->biostation_device_port = $validated['biostation_device_port'] ?? 80;
            $settings->biostation_timezone = $validated['biostation_timezone'] ?? 'UTC';
            $settings->biostation_device_name = $validated['biostation_device_name'] ?? 'Default Device';
            $settings->biostation_sync_interval = $validated['biostation_sync_interval'] ?? 5;
            $settings->biostation_last_sync = $settings->biostation_last_sync;
            $settings->manual_attendance_enabled = $request->has('manual_attendance_enabled') ? true : false;

            $settings->company_latitude = $validated['company_latitude'];
            $settings->company_longitude = $validated['company_longitude'];
        }


        if ($request->has('openai_api_key')) {
            $settings->openai_api_key = $validated['openai_api_key'];
            $settings->openai_model = $validated['openai_model'];
            $settings->chatgpt_enabled = $request->has('chatgpt_enabled');
        }

        // hr settings
        if ($request->has('payroll_disbursement_day')) {
            // إعدادات الرواتب
            $settings->daily_working_hours = $validated['daily_working_hours'];
            $settings->payroll_disbursement_day = $validated['payroll_disbursement_day'];
            // إعدادات الحضور والانصراف
            $settings->work_start_time = $validated['work_start_time'];
            $settings->work_end_time = $validated['work_end_time'];
            $settings->attendance_start_time = $validated['attendance_start_time'];
            $settings->attendance_end_time = $validated['attendance_end_time'];
            $settings->departure_start_time = $validated['departure_start_time'];
            $settings->attendance_system_locked = $request->has('attendance_system_locked');
            //  التأمينات
            $settings->insurance_percentage = $request->input('insurance_percentage');
            $settings->insurance_deduction = $request->input('insurance_deduction', 0);
            // أيام العطلة الأسبوعية
            $weeklyDaysOff = $request->input('weekly_days_off', []);

            if (empty($weeklyDaysOff)) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withFragment($request->input('active_tab'))
                    ->withErrors(['weekly_days_off' => 'يجب اختيار يوم عطلة واحد على الأقل.']);
            }

            $settings->weekly_days_off = $weeklyDaysOff;
        }


        if ($request->filled('qoyod_api_key')) {
            $settings->qoyod_api_key    = $validated['qoyod_api_key'];
            $settings->qoyod_base_url   = $validated['qoyod_base_url'];
        }


        if ($request->has('support_emails')) {
            // technical support
            $inputEmails = $request->input('support_emails', []);

            if (count($inputEmails) !== count(array_unique($inputEmails))) {
                return redirect()->back()
                    ->withInput()
                    ->withFragment($request->input('active_tab'))
                    ->with('error', 'لا يمكن إضافة بريد إلكتروني مكرر. تأكد من عدم تكرار نفس البريد.');
            }

            $settings->support_emails = count($inputEmails) > 0 ? array_unique($inputEmails) : null;
        }



        // حفظ الإعدادات
        $settings->save();
        Cache::forget('app_settings');

        return redirect()->route('general-settings.system-settings.index')->withFragment($request->input('active_tab'))->with('success', 'تم تحديث الإعدادات بنجاح');
    }


    // مرفقات المنشأة
    public function companyAttachments(CompanyAttachmentsRequest $request)
    {
        try {
            $this->service->updateCompanyAttachments($request);
            return redirect()->route('general-settings.system-settings.index')->withFragment($request->input('active_tab'))->with('success', 'تم تحديث الإعدادات بنجاح');
        } catch (\Exception $e) {
            return redirect()->route('general-settings.system-settings.index')->withFragment($request->input('active_tab'))->with('error', $e->getMessage());
        }
    }
}
