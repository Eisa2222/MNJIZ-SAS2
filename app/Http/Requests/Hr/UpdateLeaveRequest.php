<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\general_setting\SettingsLeaveType;

class UpdateLeaveRequest extends FormRequest
{
    /*
    |--------------------------------------------------------------------------
    | Authorize
    |--------------------------------------------------------------------------
    */
    public function authorize()
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
            // نوع الإجازة دائماً مطلوب
            'leave_type_id' => 'required|exists:settings_leave_types,id',

            // الملاحظات اختيارية
            'reason' => 'nullable|string|max:500',

            // مرفقات إضافية
            'additional_attachments' => 'sometimes|array',
            'additional_attachments.*.file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
            'additional_attachments.*.name' => 'nullable|string|max:255',

            // مصفوفة معرفات المرفقات للحذف
            'remove_attachments' => 'nullable|array',
            'remove_attachments.*' => 'nullable|integer|exists:leave_request_attachments,id',

        ];

        // إذا لم يتم تحديد نوع الإجازة، نطلب الحقول الأساسية
        if (!$this->filled('leave_type_id')) {
            $rules['start_date'] = 'required|date';
            $rules['end_date'] = 'required|date|after_or_equal:start_date';
            return $rules;
        }

        // نحدد ما إذا كان نوع الإجازة هو نصف يوم أم يوم كامل
        $leaveType = SettingsLeaveType::find($this->leave_type_id);
        $isHalfDay = $leaveType && $leaveType->leave_unit_type === 'half_day';

        if ($isHalfDay) {
            // قواعد للإجازة نصف يوم
            $rules['start_date'] = 'required|date';
            // لا نحتاج تاريخ النهاية لأنه سيكون نفس تاريخ البداية
        } else {
            // قواعد للإجازة بالأيام (يوم كامل)
            $rules['start_date'] = 'required|date';
            $rules['end_date'] = 'required|date|after_or_equal:start_date';
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
            'leave_type_id.required' => 'يجب اختيار نوع الإجازة.',
            'leave_type_id.exists' => 'نوع الإجازة المحدد غير موجود.',

            'start_date.required' => 'يجب تحديد تاريخ البداية.',
            'start_date.date' => 'صيغة تاريخ البداية غير صالحة.',

            'end_date.required' => 'يجب تحديد تاريخ النهاية.',
            'end_date.date' => 'صيغة تاريخ النهاية غير صالحة.',
            'end_date.after_or_equal' => 'تاريخ النهاية يجب أن يكون مساويًا أو بعد تاريخ البداية.',

            'reason.string' => 'الملاحظات يجب أن تكون نصاً.',
            'reason.max' => 'الملاحظات يجب ألا تزيد عن 500 حرف.',

            // مصفوفة المرفقات
            'additional_attachments.array' => 'حقل المرفقات يجب أن يكون مصفوفة.',
            // الملف
            'additional_attachments.*.file.required' => 'يجب اختيار ملف للمرفق رقم :position.',
            'additional_attachments.*.file.file' => 'الملف المرفوع للمرفق رقم :position غير صالح.',
            'additional_attachments.*.file.max' => 'حجم الملف للمرفق رقم :position يجب ألا يتجاوز :max كيلوبايت.',
            'additional_attachments.*.file.mimes' => 'صيغة الملف للمرفق رقم :position غير مسموح بها.',
            // الاسم
            'additional_attachments.*.name.string' => 'اسم المرفق رقم :position يجب أن يكون نصاً.',
            'additional_attachments.*.name.max' => 'اسم المرفق رقم :position يجب ألا يزيد عن :max حرفاً.',

            // مصفوفة معرفات المرفقات للحذف
            'remove_attachments.array' => 'تنسيق المرفقات المراد حذفها غير صحيح.',

        ];
    }
}
