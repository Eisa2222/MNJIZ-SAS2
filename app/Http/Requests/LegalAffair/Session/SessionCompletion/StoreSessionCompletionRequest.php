<?php

namespace App\Http\Requests\LegalAffair\Session\SessionCompletion;

use Alkoumi\LaravelHijriDate\Hijri;
use App\Enums\LegalAffair\Session\SessionCompletion\SummaryReportStatus;
use App\Models\LegalAffair\Session\Session;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSessionCompletionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // التقرير الإجمالي
            'summary_report_status'         => ['required',  Rule::in(SummaryReportStatus::values())],

            // تاريخ آخر مهلة للاعتراض (مطلوب فقط للأحكام)
            'last_objection_deadline'       => 'nullable',

            // نوع الجلسة
            'session_type'                  => 'required|exists:settings_session_types,id',

            // نوع الحكم (مطلوب فقط إذا كان نوع الجلسة = 2)
            'rule_type'                     => 'nullable|exists:settings_type_rulings,id',

            // صيغة التنفيذ (مطلوبة فقط إذا كان نوع الحكم = 1)
            'execution_format'              => 'nullable|string|in:yes,no',

            // التاريخ المتوقع للتنفيذ (مطلوب فقط إذا كانت الصيغة التنفيذية = "لا")
            'expected_execution_date'       => 'nullable',

            // دقائق التنفيذ
            'execution_minutes'             => 'required|integer|min:1|max:9999',

            // الملاحظات
            'notes'                         => 'nullable|string|max:2000',

            // المرفقات
            'session_control_attached'      => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'rule_attached'                 => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',

            // تأكيد المستخدم لإغلاق الدعوى
            'user_confirmation'             => 'nullable|string|in:نعم,لا',
        ];
    }

    public function messages(): array
    {
        return [
            // التقرير الإجمالي
            'summary_report_status.required'        => 'التقرير الإجمالي مطلوب.',
            'summary_report_status.in'              => 'قيمة التقرير الإجمالي غير صحيحة.',

            'last_objection_deadline.date'          => 'تاريخ آخر مهلة للاعتراض يجب أن يكون تاريخ صحيح.',
            'last_objection_deadline.after'         => 'تاريخ آخر مهلة للاعتراض يجب أن يكون بعد اليوم.',

            'session_type.required'                 => 'نوع الجلسة مطلوب.',
            'session_type.exists'                   => 'نوع الجلسة المحدد غير موجود.',

            'rule_type.exists'                      => 'نوع الحكم المحدد غير موجود.',

            'execution_format.in'                   => 'صيغة التنفيذ يجب أن تكون "نعم" أو "لا".',

            'expected_execution_date.date'          => 'التاريخ المتوقع للتنفيذ يجب أن يكون تاريخ صحيح.',
            'expected_execution_date.after'         => 'التاريخ المتوقع للتنفيذ يجب أن يكون بعد اليوم.',

            'execution_minutes.required'            => 'دقائق التنفيذ مطلوبة.',
            'execution_minutes.integer'             => 'دقائق التنفيذ يجب أن تكون رقم صحيح.',
            'execution_minutes.min'                 => 'دقائق التنفيذ يجب أن تكون على الأقل 1.',
            'execution_minutes.max'                 => 'دقائق التنفيذ لا يجب أن تتجاوز 9999.',

            'notes.string'                          => 'الملاحظات يجب أن تكون نص.',
            'notes.max'                             => 'الملاحظات لا يجب أن تتجاوز 2000 حرف.',

            'session_control_attached.file'         => 'مرفق ضبط الجلسة يجب أن يكون ملف.',
            'session_control_attached.mimes'        => 'مرفق ضبط الجلسة يجب أن يكون من نوع: pdf, doc, docx, jpg, jpeg, png.',
            'session_control_attached.max'          => 'مرفق ضبط الجلسة لا يجب أن يتجاوز 10 ميجابايت.',

            'rule_attached.file'                    => 'مرفق الحكم يجب أن يكون ملف.',
            'rule_attached.mimes'                   => 'مرفق الحكم يجب أن يكون من نوع: pdf, doc, docx, jpg, jpeg, png.',
            'rule_attached.max'                     => 'مرفق الحكم لا يجب أن يتجاوز 10 ميجابايت.',

            'user_confirmation.in'                  => 'تأكيد المستخدم يجب أن يكون "نعم" أو "لا".',
        ];
    }

    public function attributes(): array
    {
        return [
            'summary_report_status'         => 'التقرير الإجمالي',
            'last_objection_deadline'       => 'تاريخ آخر مهلة للاعتراض',
            'session_type'                  => 'نوع الجلسة',
            'rule_type'                     => 'نوع الحكم',
            'execution_format'              => 'صيغة التنفيذ',
            'expected_execution_date'       => 'التاريخ المتوقع للتنفيذ',
            'execution_minutes'             => 'دقائق التنفيذ',
            'notes'                         => 'الملاحظات',
            'session_control_attached'      => 'مرفق ضبط الجلسة',
            'rule_attached'                 => 'مرفق الحكم',
            'user_confirmation'             => 'تأكيد المستخدم',
        ];
    }


    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $summaryStatus = SummaryReportStatus::tryFrom($this->summary_report_status);

            // تحقق من تاريخ آخر مهلة للاعتراض
            if ($summaryStatus && $summaryStatus->requiresObjectionDeadline()) {
                if (empty($this->last_objection_deadline)) {
                    $validator->errors()->add('last_objection_deadline', 'تاريخ آخر مهلة للاعتراض مطلوب للأحكام.');
                }
            }




            // تحقق من نوع الحكم
            if ($this->session_type == '2') {
                if (empty($this->rule_type)) {
                    $validator->errors()->add('rule_type', 'نوع الحكم مطلوب عند اختيار جلسة حكم.');
                }
            }

            // تحقق من صيغة التنفيذ
            if ($this->rule_type == '1') {
                if (empty($this->execution_format)) {
                    $validator->errors()->add('execution_format', 'صيغة التنفيذ مطلوبة للأحكام النهائية.');
                }
            }

            // تحقق من التاريخ المتوقع للتنفيذ
            if ($this->execution_format === 'no') {
                if (empty($this->expected_execution_date)) {
                    $validator->errors()->add('expected_execution_date', 'التاريخ المتوقع للتنفيذ مطلوب عند اختيار "لا" للصيغة التنفيذية.');
                }
            }

            // // تحقق من مرفق الحكم
            // if ($this->session_type == '2' && !$this->hasFile('rule_attached') && empty($this->existing_rule_attached)) {
            //     $validator->errors()->add('rule_attached', 'مرفق الحكم مطلوب لجلسات الأحكام.');
            // }
        });
    }
}
