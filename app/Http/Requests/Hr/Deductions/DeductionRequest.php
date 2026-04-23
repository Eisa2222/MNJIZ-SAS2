<?php

namespace App\Http\Requests\Hr\Deductions;

use App\Enums\Hr\Deduction\DeductionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeductionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id'       => ['required', 'exists:employees,id'],
            'deduction_type'    => ['required', Rule::in(DeductionType::values()),],
            'amount'            => ['required', 'numeric','min:0.01'],
            'deduction_date'    => ['required', 'date'],
            'notes'             => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required'          => 'اختر الموظف أولاً.',
            'employee_id.exists'            => 'الموظف المحدد غير موجود.',
            'deduction_type.required'       => 'حدد نوع الخصم المالي.',
            'deduction_type.in'             => 'نوع الخصم المالي غير صالح.',
            'amount.required'               => 'أدخل مبلغ الخصم المالي.',
            'amount.numeric'                => 'القيمة يجب أن تكون رقما.',
            'amount.min'                    => 'القيمة لا يمكن أن تقل عن 0.01.',
            'deduction_date.required'       => 'حدد تاريخ الخصم المالي.',
            'deduction_date.date'           => 'تاريخ الخصم المالي غير صالح.',
            'notes.string'                  => 'الوصف/الملاحظات يجب أن تكون نصاً.',
            'notes.max'                     => 'الوصف/الملاحظات لا يمكن أن تتجاوز 500 حرف.',
        ];
    }
}
