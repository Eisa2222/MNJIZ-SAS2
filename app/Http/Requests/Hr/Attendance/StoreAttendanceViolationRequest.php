<?php

namespace App\Http\Requests\Hr\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Hr\Attendance\Attendance;
use Carbon\Carbon;

class StoreAttendanceViolationRequest extends FormRequest
{
    /*
    |--------------------------------------------------------------------------
    | Determine if the user is authorized to make this request.
    |--------------------------------------------------------------------------
    |
    | هنا يتم تحديد ما إذا كان المستخدم مصرحًا له بتنفيذ هذا الطلب.
    | يمكن استخدام قواعد التصريح هنا للتحقق من صلاحيات المستخدم.
    |
    */
    public function authorize()
    {
        return true; // أو يمكن استخدام قواعد التصريح الخاصة بك
    }

    /*
    |--------------------------------------------------------------------------
    | Get the validation rules that apply to the request.
    |--------------------------------------------------------------------------
    |
    | هنا يتم تحديد قواعد التحقق الخاصة بطلب تسجيل مخالفة الحضور.
    | يتم تحديد جميع القواعد المطلوبة لحقول مخالفة الحضور.
    |
    */
    public function rules()
    {
        return [
            'attendance_id'         => 'required|exists:attendances,id',
            'settings_violation_id' => 'required|exists:settings_violations,id',
            'violation_date'        => 'nullable|date',
            'is_appealable'         => 'nullable|boolean',
            'appeal_days'           => 'nullable|integer|min:1',
            'notes'                 => 'nullable|string',
            'attachment'            => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Prepare the data for validation.
    |--------------------------------------------------------------------------
    |
    | هنا يتم تجهيز البيانات قبل التحقق منها.
    | نقوم باستخراج معرف الموظف من سجل الحضور وإضافته للطلب.
    |
    */
    protected function prepareForValidation()
    {
        if ($this->has('attendance_id')) {
            $attendance = Attendance::find($this->attendance_id);
            if ($attendance) {
                $this->merge([
                    'user_id' => $attendance->user_id,
                    'violation_date' => $this->violation_date ??
                        Carbon::parse($attendance->date)->format('Y-m-d') . ' ' . now()->format('H:i:s')
                ]);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get custom messages for validator errors.
    |--------------------------------------------------------------------------
    |
    | هنا يتم تحديد الرسائل المخصصة لأخطاء التحقق.
    | يتم استخدام هذه الرسائل بدلاً من الرسائل الافتراضية.
    |
    */
    public function messages()
    {
        return [
            'attendance_id.required'        => 'حقل الحضور مطلوب.',
            'attendance_id.exists'          => 'الحضور المحدد غير موجود.',
            'settings_violation_id.required' => 'حقل المخالفة مطلوب.',
            'settings_violation_id.exists' => 'المخالفة المحددة غير موجودة.',
            'violation_date.date' => 'تاريخ المخالفة يجب أن يكون تاريخًا صالحًا.',
            'is_appealable.boolean' => 'حقل السماح بالتظلم يجب أن يكون قيمة منطقية.',
            'appeal_days.integer' => 'عدد أيام التظلم يجب أن يكون رقمًا صحيحًا.',
            'appeal_days.min' => 'عدد أيام التظلم يجب أن يكون على الأقل :min.',
            'notes.string' => 'الملاحظات يجب أن تكون نصًا.',
            'attachment.file' => 'المرفق يجب أن يكون ملفًا.',
            'attachment.mimes' => 'المرفق يجب أن يكون من نوع: :values.',
            'attachment.max' => 'حجم المرفق لا يجب أن يتجاوز :max كيلوبايت.',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Get custom attributes for validator errors.
    |--------------------------------------------------------------------------
    |
    | هنا يتم تحديد الأسماء المخصصة للحقول المستخدمة في رسائل الخطأ.
    | يتم استخدام هذه الأسماء بدلاً من أسماء الحقول الأصلية.
    |
    */
    public function attributes()
    {
        return [
            'attendance_id' => 'الحضور',
            'settings_violation_id' => 'المخالفة',
            'violation_date' => 'تاريخ المخالفة',
            'is_appealable' => 'السماح بالتظلم',
            'appeal_days' => 'أيام التظلم',
            'notes' => 'الملاحظات',
            'attachment' => 'المرفق',
        ];
    }
}
