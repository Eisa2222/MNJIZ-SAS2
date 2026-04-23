<?php

namespace App\Http\Requests\Hr\ViolationsPenalties;

use Illuminate\Foundation\Http\FormRequest;

class StoreViolationsPenalties extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'employee_id'             => ['required', 'integer', 'exists:employees,id'],
            'settings_violation_id'   => ['required', 'integer', 'exists:settings_violations,id'],
            'violation_date'          => ['required', 'date', 'before_or_equal:now'],
            'notes'                   => ['nullable', 'string'],
            'is_appealable'           => ['sometimes', 'boolean'],
            'appeal_days'             => ['sometimes', 'integer', 'min:0'],
        ];
    }


    public function messages(): array
    {
        return [
            // employee_id
            'employee_id.required'   => 'حقل الموظف مطلوب.',
            'employee_id.integer'    => 'رقم الموظف يجب أن يكون رقماً صحيحاً.',
            'employee_id.exists'     => 'الموظف المحدد غير موجود.',

            // settings_violation_id
            'settings_violation_id.required' => 'حقل نوع المخالفة مطلوب.',
            'settings_violation_id.integer'  => 'رقم نوع المخالفة يجب أن يكون رقماً صحيحاً.',
            'settings_violation_id.exists'   => 'نوع المخالفة المحدد غير موجود.',

            // violation_date
            'violation_date.required'        => 'حقل تاريخ المخالفة مطلوب.',
            'violation_date.date'            => 'تاريخ المخالفة غير صالح.',
            'violation_date.before_or_equal' => 'لا يمكن تحديد تاريخ مستقبلي للمخالفة.',

            // notes
            'notes.string'                   => 'الملاحظات يجب أن تكون نصاً.',

            // is_appealable
            'is_appealable.boolean'          => 'قيمة قابلية التظلم يجب أن تكون صحيحة أو خاطئة.',

            // appeal_days
            'appeal_days.integer'            => 'عدد أيام التظلم يجب أن يكون رقماً صحيحاً.',
            'appeal_days.min'                => 'عدد أيام التظلم يجب ألا يكون أقل من صفر.',
        ];
    }
}
