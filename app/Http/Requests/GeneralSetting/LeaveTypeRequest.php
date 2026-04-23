<?php

namespace App\Http\Requests\GeneralSetting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeaveTypeRequest extends FormRequest
{

    /*
    |--------------------------------------------------------------------------
    | Authorize
    |--------------------------------------------------------------------------
    | Determine if the user is authorized to make this request.
    */
    public function authorize(): bool
    {
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Rules
    |--------------------------------------------------------------------------
    */
    public function rules()
    {
        $rules = [
            'name'                     => ['required', 'string'],
            'days'                     => ['nullable', 'integer', 'min:0'],
            'is_paid'                  => ['boolean'],
            'is_carry_forwardable'     => ['boolean'],
            'is_deductible'            => ['boolean'],
            'is_global'                => ['boolean'],
            'count_weekends'           => ['boolean'],
            'leave_unit_type'          => ['required', Rule::in(['full_day', 'half_day'])], // الحقل الجديد
            'gender_applicability'     => ['required', Rule::in(['both', 'female'])],
            'min_service_years'        => ['nullable', 'integer', 'min:0'],
            'has_attachments'          => ['boolean'],
            'start_date'               => ['nullable', 'date'],
            'end_date'                 => ['nullable', 'date', 'after_or_equal:start_date'],
            'max_requests'             => ['nullable', 'integer', 'min:0'],
            'service_years_threshold'  => ['nullable', 'integer', 'min:0'],
            'days_after_threshold'     => ['nullable', 'integer', 'min:0'],
            'advance_notice_days'     => ['nullable', 'integer', 'min:0'],
        ];


        // شرطياً نضيف التحقق لحقل الوصف
        if ($this->boolean('has_attachments')) {
            $rules['attachment_description'] = ['required', 'string'];
        } else {
            // إذا لم تُفعل المرفقات، نجعل الوصف قابلًا لأن يكون فارغًا
            $rules['attachment_description'] = ['nullable', 'string'];
        }

        if ($this->isMethod('post')) {
            $rules['name'][] = Rule::unique('settings_leave_types')->whereNull('deleted_at');
        } elseif ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['name'][] = Rule::unique('settings_leave_types')
                ->ignore($this->route('settings_leave_type'))
                ->whereNull('deleted_at');
        }


        if ($this->boolean('is_global')) {
            $rules['start_date'] = ['required', 'date'];
            $rules['end_date'] = ['required', 'date', 'after_or_equal:start_date'];
        }

        return $rules;
    }

    /*
    |--------------------------------------------------------------------------
    | Messages
    |--------------------------------------------------------------------------
    */
    public function messages()
    {
        return [
            'name.required'         => 'حقل الاسم مطلوب.',
            'name.string'           => 'يجب أن يكون الاسم نصًا.',
            'name.unique'           => 'الاسم مسجل من قبل.',
            // 'days.required'         => 'حقل الأيام مطلوب.',
            'days.integer'          => 'يجب أن يكون عدد الأيام رقمًا صحيحًا.',
            'days.min'              => 'يجب ألا يكون عدد الأيام سالبًا.',
            'is_paid.boolean'       => 'يجب أن تكون قيمة "مدفوعة" إما صحيحة أو خاطئة.',
            'is_carry_forwardable.boolean' => 'قيمة "الترحيل السنوي" يجب أن تكون صحيحة أو خاطئة.',
            'is_deductible.boolean'           => 'قيمة "خصم من الرصيد" يجب أن تكون صحيحة أو خاطئة.',
            'is_global.boolean'             => 'قيمة "عالمية" يجب أن تكون صحيحة أو خاطئة.',
            'count_weekends.boolean' => 'قيمة "احتساب أيام الإجازة الأسبوعية" يجب أن تكون صحيحة أو خاطئة.',
            'leave_unit_type.required' => 'يجب تحديد وحدة احتساب الإجازة.',
            'leave_unit_type.in'       => 'وحدة احتساب الإجازة يجب أن تكون أيام كاملة أو نصف يوم.',
            'min_service_years.integer' => 'يجب أن تكون سنوات الخدمة عددًا صحيحًا.',
            'min_service_years.min'     => 'لا يمكن أن تكون سنوات الخدمة سلبية.',
            'has_attachments.boolean' => 'قيمة "المرفقات" يجب أن تكون صحيحة أو خاطئة.',
            'attachment_description.required' => 'حدد نوع المرفقات المطلوبة.',
            'attachment_description.string'   => 'يجب أن يكون وصف المرفقات نصًا.',
            'start_date.date'       => 'يجب أن يكون تاريخ البداية تاريخًا صالحًا.',
            'end_date.date'         => 'يجب أن يكون تاريخ النهاية تاريخًا صالحًا.',
            'end_date.after_or_equal' => 'يجب أن يكون تاريخ النهاية مساويًا أو بعد تاريخ البداية.',
            'max_requests.integer' => 'يجب أن يكون الحد الأقصى لعدد الطلبات عددًا صحيحًا.',
            'max_requests.min'     => 'يجب أن تكون قيمة الحد الأقصى ≥ 0.',
            'service_years_threshold.integer'   => 'يجب أن تكون قيمة عتبة سنوات الخدمة عددًا صحيحًا.',
            'service_years_threshold.min'       => 'لا يمكن أن تكون قيمة عتبة سنوات الخدمة سلبية.',
            'days_after_threshold.integer'      => 'يجب أن تكون قيمة أيام ما بعد العتبة عددًا صحيحًا.',
            'days_after_threshold.min'          => 'لا يمكن أن تكون قيمة أيام ما بعد العتبة سلبية.',
            'advance_notice_days.integer'      => 'يجب أن يكون الإشعار المسبق عددًا صحيحًا.',
            'advance_notice_days.min'          => 'لا يمكن أن يكون الإشعار المسبق سلبيًا.',
            'start_date.required' => 'عند تحديد أن الإجازة عامة، يجب تحديد تاريخ البداية.',
            'end_date.required' => 'عند تحديد أن الإجازة عامة، يجب تحديد تاريخ النهاية.',

        ];
    }
}
