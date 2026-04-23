<?php

namespace App\Http\Requests\Hr\Rewards;

use App\Enums\Hr\Reward\RewardType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RewardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id'   => ['required', 'exists:employees,id'],
            'reward_type'   => ['required', Rule::in(RewardType::values()),],
            'amount'        => ['required', 'numeric','min:0.01'],
            'reward_date'   => ['required', 'date'],
            'notes'         => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required'      => 'اختر الموظف أولاً.',
            'employee_id.exists'        => 'الموظف المحدد غير موجود.',
            'reward_type.required'      => 'حدد نوع المكافأة.',
            'reward_type.in'            => 'نوع المكافأة غير صالح.',
            'amount.required'           => 'أدخل مبلغ المكافأة.',
            'amount.numeric'            => 'القيمة يجب أن تكون رقما.',
            'amount.min'                => 'القيمة لا يمكن أن تقل عن 0.01.',
            'reward_date.required'      => 'حدد تاريخ المكافأة.',
            'reward_date.date'          => 'تاريخ المكافأة غير صالح.',
            'notes.string'              => 'الوصف/الملاحظات يجب أن تكون نصاً.',
            'notes.max'                 => 'الوصف/الملاحظات لا يمكن أن تتجاوز 500 حرف.',
        ];
    }
}
