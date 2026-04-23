<?php

namespace App\Http\Requests\GeneralSetting\SystemSetting;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;

class MainSettingsRequest extends FormRequest
{

    /*
    |--------------------------------------------------------------------------
    | authorize the request
    |--------------------------------------------------------------------------
    */
    public function authorize(): bool
    {
        return true;
    }



    /*
    |--------------------------------------------------------------------------
    | rules for validation
    |--------------------------------------------------------------------------
    */
    public function rules(): array
    {
        return [
            // general settings
            'office_name'                   => 'sometimes|required|string|max:255',
            'image'                         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:800',
            'archive_delete_duration'       => 'sometimes|required',
            'logo_text'                     => 'sometimes|required|string|max:14',
            'maintenance_mode'              => 'nullable',

            // printing settings
            'horizontal_header_image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1200',
            'horizontal_footer_image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1200',
            'signature'                     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:500',

            //microsft settings
            'microsoft_client_id'           => 'sometimes|required|string',
            'microsoft_client_secret'       => 'sometimes|required|string',
            'microsoft_redirect_uri'        => 'sometimes|required|url',
            'microsoft_tenant_id'           => 'sometimes|required|string',
            'main_email'                    => 'sometimes|required|email',

            //sms settings
            'sms_provider'                  => 'sometimes|required|string|max:255',
            'sms_api_key'                   => 'sometimes|required|string|max:255',
            'sms_api_secret'                => 'sometimes|required|string|max:255',
            'sms_sender_id'                 => 'sometimes|required|string|max:255',
            'sms_enabled'                   => 'nullable',

            // whatsapp settings
            'twilio_account_sid'            => 'sometimes|required|string',
            'twilio_auth_token'             => 'sometimes|required|string',
            'twilio_whatsapp_from'          => 'sometimes|required|string',
            'whatsapp_enabled'              => 'nullable',

            // biostation settings
            'biostation_api_key'            => 'nullable|string',
            'biostation_api_url'            => 'nullable|url',
            'biostation_device_ip'          => 'nullable|ip',
            'biostation_device_port'        => 'nullable|integer|min:1|max:65535',
            'biostation_timezone'           => 'nullable|string',
            'biostation_device_name'        => 'nullable|string|max:255',
            'biostation_sync_interval'      => 'nullable|integer|min:1|max:60',
            'manual_attendance_enabled'     => 'sometimes',
            'company_latitude'              => 'nullable|numeric|between:-90,90',
            'company_longitude'             => 'nullable|numeric|between:-180,180',

            // openai settings
            'openai_api_key'                => 'sometimes|required|string',
            'openai_model'                  => 'sometimes|required|string|in:gpt-4o,gpt-4o-mini,gpt-4-turbo,gpt-3.5-turbo',

            // hr settings
            'daily_working_hours'           => 'nullable|numeric|min:1|max:24',
            'payroll_disbursement_day'      => 'sometimes|required|integer|min:1|max:28',
            'work_start_time'               => 'sometimes|required',
            'work_end_time'                 => 'sometimes|required|after:work_start_time',
            'weekly_days_off'               => 'sometimes|required|array', // تغيير إلى nullable كقاعدة افتراضية
            'weekly_days_off.*'             => 'nullable|in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
            'attendance_start_time'         => 'sometimes|required',
            'attendance_end_time'           => 'sometimes|required',
            'departure_start_time'          => 'sometimes|required',
            'attendance_system_locked'      => 'nullable',
            'insurance_deduction'           => 'nullable|in:0,1',
            'insurance_percentage'          => 'nullable|numeric|between:0,100',

            // qoyod settings 
            'qoyod_api_key'                 => 'nullable|string|max:255',
            'qoyod_base_url'                => 'nullable|string|url|max:255',

            // Support
            'support_emails.*'              => 'nullable|email',

            // linkedin settings
            'linkedin_client_id'            => 'nullable|string|max:1000',
            'linkedin_client_secret'        => 'nullable|string|max:1000',
            'linkedin_access_token'         => 'nullable|string|max:1000',


        ];

        return $rules;
    }


    /*
    |--------------------------------------------------------------------------
    | messages for validation
    |--------------------------------------------------------------------------
    */
    public function messages()
    {
        return [
            // إعدادات عامة
            'office_name.required'                  => 'حقل اسم المكتب مطلوب.',
            'office_name.string'                    => 'حقل اسم المكتب يجب أن يكون نصاً.',
            'office_name.max'                       => 'حقل اسم المكتب يجب ألا يتجاوز 255 حرفاً.',
            'image.image'                           => 'حقل الصورة يجب أن يكون صورة.',
            'image.mimes'                           => 'حقل الصورة يجب أن يكون من نوع jpeg، png، jpg، gif.',
            'image.max'                             => 'حجم الصورة يجب ألا يتجاوز 800 كيلوبايت.',
            'archive_delete_duration.required'      => 'حقل مدة حذف الأرشيف مطلوب.',
            'logo_text.string'                      => 'حقل نص الشعار يجب أن يكون نصاً.',
            'logo_text.max'                         => 'حقل نص الشعار يجب ألا يتجاوز 14 حرفاً.',
            'maintenance_mode.boolean'              => 'حقل وضع الصيانة يجب أن يكون صحيحاً أو خاطئاً.',

            // إعدادات الطباعة
            // الترويسة
            'horizontal_header_image.required'      => 'حقل الترويسة  مطلوب.',
            'horizontal_header_image.image'         => 'حقل الترويسة  يجب أن يكون صورة.',
            'horizontal_header_image.mimes'         => 'حقل الترويسة  يجب أن يكون من نوع jpeg، png، jpg، gif.',
            'horizontal_header_image.max'           => 'حجم الترويسة  يجب ألا يتجاوز 1200 كيلوبايت.',

            // الفوتر
            'horizontal_footer_image.required'      => 'حقل الفوتر  مطلوب.',
            'horizontal_footer_image.image'         => 'حقل الفوتر  يجب أن يكون صورة.',
            'horizontal_footer_image.mimes'         => 'حقل الفوتر  يجب أن يكون من نوع jpeg، png، jpg، gif.',
            'horizontal_footer_image.max'           => 'حجم الفوتر  يجب ألا يتجاوز 1200 كيلوبايت.',

            // الختم
            'signature.required'                    => 'حقل الختم مطلوب.',
            'signature.image'                       => 'حقل الختم يجب أن يكون صورة.',
            'signature.mimes'                       => 'حقل الختم يجب أن يكون من نوع jpeg، png، jpg، gif.',
            'signature.max'                         => 'حجم الختم يجب ألا يتجاوز 500 كيلوبايت.',


            // إعدادات Microsoft
            'microsoft_client_id.required'          => 'حقل معرف العميل لـ Microsoft مطلوب.',
            'microsoft_client_id.string'            => 'حقل معرف العميل لـ Microsoft يجب أن يكون نصاً.',
            'microsoft_client_secret.required'      => 'حقل سر العميل لـ Microsoft مطلوب.',
            'microsoft_client_secret.string'        => 'حقل سر العميل لـ Microsoft يجب أن يكون نصاً.',
            'microsoft_redirect_uri.required'       => 'حقل URI إعادة التوجيه لـ Microsoft مطلوب.',
            'microsoft_redirect_uri.url'            => 'حقل URI إعادة التوجيه لـ Microsoft يجب أن يكون رابطاً صالحاً.',
            'microsoft_tenant_id.required'          => 'حقل معرف المستأجر لـ Microsoft مطلوب.',
            'microsoft_tenant_id.string'            => 'حقل معرف المستأجر لـ Microsoft يجب أن يكون نصاً.',
            'main_email.required'                   => 'حقل البريد الإلكتروني الرئيسي مطلوب.',
            'main_email.email'                      => 'حقل البريد الإلكتروني الرئيسي يجب أن يكون بريد إلكتروني صالحاً.',

            // إعدادات الرسائل النصية
            'sms_provider.string'                   => 'حقل مزود الرسائل النصية يجب أن يكون نصاً.',
            'sms_provider.max'                      => 'حقل مزود الرسائل النصية يجب ألا يتجاوز 255 حرفاً.',
            'sms_api_key.string'                    => 'حقل مفتاح API للرسائل النصية يجب أن يكون نصاً.',
            'sms_api_key.max'                       => 'حقل مفتاح API للرسائل النصية يجب ألا يتجاوز 255 حرفاً.',
            'sms_api_secret.string'                 => 'حقل سر API للرسائل النصية يجب أن يكون نصاً.',
            'sms_api_secret.max'                    => 'حقل سر API للرسائل النصية يجب ألا يتجاوز 255 حرفاً.',
            'sms_sender_id.string'                  => 'حقل معرف المرسل للرسائل النصية يجب أن يكون نصاً.',
            'sms_sender_id.max'                     => 'حقل معرف المرسل للرسائل النصية يجب ألا يتجاوز 255 حرفاً.',
            'sms_enabled.boolean'                   => 'حقل تمكين الرسائل النصية يجب أن يكون صحيحاً أو خاطئاً.',

            // إعدادات الواتساب
            'twilio_account_sid.required'           => 'حقل معرف حساب Twilio مطلوب.',
            'twilio_account_sid.string'             => 'حقل معرف حساب Twilio يجب أن يكون نصاً.',
            'twilio_auth_token.required'            => 'حقل رمز توثيق Twilio مطلوب.',
            'twilio_auth_token.string'              => 'حقل رمز توثيق Twilio يجب أن يكون نصاً.',
            'twilio_whatsapp_from.required'         => 'حقل من Twilio WhatsApp مطلوب.',
            'twilio_whatsapp_from.string'           => 'حقل من Twilio WhatsApp يجب أن يكون نصاً.',
            'whatsapp_enabled.boolean'              => 'حقل تمكين WhatsApp يجب أن يكون صحيحاً أو خاطئاً.',

            // biostation settings
            'biostation_api_key.string'             => 'مفتاح API يجب أن يكون نصًا.',
            'biostation_api_url.url'                => 'عنوان API يجب أن يكون عنوان URL صالحًا.',
            'biostation_device_ip.ip'               => 'عنوان IP للجهاز يجب أن يكون عنوان IP صالحًا.',
            'biostation_device_port.integer'        => 'منفذ الجهاز يجب أن يكون عددًا صحيحًا.',
            'biostation_device_port.min'            => 'منفذ الجهاز يجب أن يكون على الأقل 1.',
            'biostation_device_port.max'            => 'منفذ الجهاز لا يمكن أن يتجاوز 65535.',
            'biostation_timezone.string'            => 'المنطقة الزمنية يجب أن تكون نصًا.',
            'biostation_device_name.string'         => 'اسم الجهاز يجب أن يكون نصًا.',
            'biostation_device_name.max'            => 'اسم الجهاز لا يمكن أن يتجاوز 255 حرفًا.',
            'biostation_sync_interval.integer'      => 'فترة التزامن يجب أن تكون عددًا صحيحًا.',
            'biostation_sync_interval.min'          => 'فترة التزامن يجب أن تكون على الأقل 1 دقيقة.',
            'biostation_sync_interval.max'          => 'فترة التزامن لا يمكن أن تتجاوز 60 دقيقة.',
            'work_end_time.after'                   => 'يجب أن يكون وقت نهاية العمل بعد وقت بداية العمل.',
            'company_latitude.between'              => 'يجب أن تكون قيمة خط العرض بين -90 و 90 درجة.',
            'company_longitude.between'             => 'يجب أن تكون قيمة خط الطول بين -180 و 180 درجة.',
            'work_start_time.required'              => 'يجب تحديد وقت بداية العمل.',
            'work_end_time.required'                => 'يجب تحديد وقت نهاية العمل.',
            'work_end_time.after'                   => 'يجب أن يكون وقت نهاية العمل بعد وقت بداية العمل.',
            'company_latitude.numeric'              => 'يجب أن تكون قيمة خط العرض رقمية.',
            'company_latitude.between'              => 'يجب أن تكون قيمة خط العرض بين -90 و 90 درجة.',
            'company_longitude.numeric'             => 'يجب أن تكون قيمة خط الطول رقمية.',
            'company_longitude.between'             => 'يجب أن تكون قيمة خط الطول بين -180 و 180 درجة.',

            // openai settings
            'openai_api_key.required'               => 'يجب تحديد مفتاح API لـ OpenAI إذا كان الحقل مفعل.',
            'openai_api_key.string'                 => 'يجب أن يكون مفتاح API لـ OpenAI نصًا.',
            'openai_model.required'                 => 'يجب تحديد نموذج OpenAI إذا كان الحقل مفعل.',
            'openai_model.string'                   => 'يجب أن يكون نموذج OpenAI نصًا.',
            'openai_model.in'                       => 'النموذج المحدد غير صالح. الرجاء اختيار نموذج صحيح من القائمة المتاحة.',

            // hr settings
            'daily_working_hours.numeric'           => 'يجب أن تكون ساعات العمل اليومية رقمية.',
            'daily_working_hours.min'               => 'يجب أن تكون ساعات العمل اليومية على الأقل 1.',
            'daily_working_hours.max'               => 'يجب ألا تتجاوز ساعات العمل اليومية 24.',
            'weekly_days_off.required'              => 'يجب تحديد أيام العطلة الأسبوعية.',
            'weekly_days_off.array'                 => 'يجب أن تكون أيام العطلة الأسبوعية مصفوفة.',
            'weekly_days_off.min'                   => 'يجب اختيار يوم عطلة واحد على الأقل.',
            'weekly_days_off.*.in'                  => 'يجب أن تكون أيام العطلة الأسبوعية من بين القيم التالية: الأحد، الاثنين، الثلاثاء، الأربعاء، الخميس، الجمعة، السبت.',
            'payroll_disbursement_day.required'     => 'يرجى إدخال يوم صرف المرتب.',
            'payroll_disbursement_day.integer'      => 'يجب أن يكون يوم صرف المرتب رقماً صحيحاً.',
            'payroll_disbursement_day.min'          => 'يجب أن يكون يوم صرف المرتب على الأقل 1.',
            'payroll_disbursement_day.max'          => 'يجب ألا يتجاوز يوم صرف المرتب 28 لكي يكون متوافقاً مع كل الشهور.',
            'attendance_start_time.required'        => 'يجب تحديد وقت بداية تسجيل الحضور.',
            'attendance_end_time.required'          => 'يجب تحديد وقت نهاية تسجيل الحضور.',
            'departure_start_time.required'         => 'يجب تحديد وقت بداية تسجيل الانصراف.',
            'attendance_system_locked.boolean'      => 'حقل قفل نظام الحضور والانصراف يجب أن يكون صحيحاً أو خاطئاً.',
            'insurance_deduction.in'                => 'قيمة غير صالحة لحقل تفعيل حسم التأمينات.',

            'insurance_percentage.numeric'          => 'حقل نسبة التأمين يجب أن يكون رقمًا.',
            'insurance_percentage.between'          => 'حقل نسبة التأمين يجب أن يكون بين 0% و 100%.',

            'qoyod_api_key.string'                  =>  'مفتاح API لـ Qoyod يجب أن يكون نصًا.',
            'qoyod_api_key.max'                     => 'Api Key لـ Qoyod يجب ألا يتجاوز 255 حرفًا.',

            'qoyod_base_url.string'                 => 'رابط Qoyod يجب أن يكون نصًا.',
            'qoyod_base_url.max'                    => 'رابط Qoyod يجب ألا يتجاوز 255 حرفًا.',
            'qoyod_base_url.url'                    => 'رابط Qoyod يجب أن يكون رابطًا صالحًا.',

            'support_emails.array'                  => 'يجب أن تكون قائمة البريد الإلكتروني للدعم مصفوفة.',
            'support_emails.*.email'                => 'يجب أن يكون كل بريد إلكتروني في قائمة الدعم بريد إلكتروني صالح.',

            // إعدادات لينكد إن
            'linkedin_client_id.string'             => 'معرف عميل لينكد إن يجب أن يكون نصًا.',
            'linkedin_client_id.max'                => 'معرف عميل لينكد إن يجب ألا يتجاوز 1000 حرف.',
            'linkedin_client_secret.string'         => 'سر عميل لينكد إن يجب أن يكون نصًا.',
            'linkedin_client_secret.max'                => 'سر عميل لينكد إن يجب ألا يتجاوز 1000 حرف.',
            'linkedin_access_token.string'          => 'رمز وصول لينكد إن يجب أن يكون نصًا.',
            'linkedin_access_token.max'             => 'رمز وصول لينكد إن يجب ألا يتجاوز 1000 حرف.',
            

        ];
    }



    /*
    |--------------------------------------------------------------------------
    | redirect back with errors
    |--------------------------------------------------------------------------
    */
    protected function failedValidation(Validator $validator)
    {
        $activeTab = $this->input('active_tab', '#generalSettings');

        throw new HttpResponseException(
            redirect()
                ->back()
                ->withInput()
                ->withFragment(ltrim($activeTab, '#'))
                ->withErrors($validator)
        );
    }
}
