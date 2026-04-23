<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;

class OpeningBalanceRequest extends FormRequest
{
    /*
    |--------------------------------------------------------------------------
    | Authorization
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
        return [
            'employee_id'    => 'required|exists:employees,id',
            'leave_type_id'  => 'required|exists:settings_leave_types,id',
            'opening_balance'         => 'required|numeric|min:0|max:999999.99',
            'effective_date' => 'required|date',
            'note'           => 'nullable|string|max:255',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Messages
    |--------------------------------------------------------------------------
    */
    public function messages()
    {
        return [
            'employee_id.required'    => 'يجب تحديد الموظف.',
            'employee_id.exists'      => 'الموظف المحدد غير موجود.',
            'leave_type_id.required'  => 'يجب تحديد نوع الإجازة.',
            'leave_type_id.exists'    => 'نوع الإجازة المحدد غير موجود.',
            'opening_balance.required'         => 'يجب إدخال كمية الرصيد.',
            'opening_balance.numeric'          => 'يجب أن تكون الكمية رقمية.',
            'opening_balance.min'              => 'يجب أن تكون الكمية صفر أو أكبر.',
            'opening_balance.max'              => 'الكمية المدخلة كبيرة جدًا. الحد الأقصى هو 999999.99.',
            'effective_date.required' => 'يجب تحديد تاريخ التفعيل.',
            'effective_date.date'     => 'صيغة التاريخ غير صحيحة.',
            'note.max'                => 'الملاحظة يجب أن لا تتجاوز 255 حرفاً.',
        ];
    }
}
