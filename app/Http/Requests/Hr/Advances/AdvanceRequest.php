<?php

namespace App\Http\Requests\Hr\Advances;

use App\Enums\Hr\Advance\AdvanceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdvanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'employee_id'   => ['required', 'exists:employees,id'],
            'advance_type'  => ['required',  Rule::in(AdvanceType::values())],
            'amount'        => ['required',  'numeric','min:0.01'],
            'advance_date'  => ['required',  'date'],
            'due_date'      => ['nullable',  'date', 'after_or_equal:advance_date'],
            'notes'         => ['nullable',  'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required'      => 'اختر الموظف أولاً.',
            'employee_id.exists'        => 'الموظف المحدد غير موجود.',
            'advance_type.required'     => 'حدد نوع السلفة.',
            'advance_type.in'           => 'نوع السلفة غير صالح.',
            'amount.required'           => 'أدخل مبلغ السلفة.',
            'amount.numeric'            => 'المبلغ يجب أن يكون رقمياً.',
            'amount.min'                => 'المبلغ لا يمكن أن يقل عن 0.01.',
            'advance_date.required'     => 'حدد تاريخ السلفة.',
            'advance_date.date'         => 'تاريخ السلفة غير صالح.',
            'due_date.date'             => 'تاريخ الاستحقاق غير صالح.',
            'due_date.after_or_equal'   => 'تاريخ الاستحقاق يجب أن يكون بعد أو يساوي تاريخ السلفة.',
            'notes.string'              => 'الملاحظات يجب أن تكون نصاً.',
            'notes.max'                 => 'الملاحظات لا يمكن أن تتجاوز 500 حرف.',
        ];
    }
}
