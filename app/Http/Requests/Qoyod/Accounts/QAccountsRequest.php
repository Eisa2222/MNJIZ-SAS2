<?php

namespace App\Http\Requests\Qoyod\Accounts;

use App\Enums\Qoyod\Accounts\AccountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QAccountsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_ar'           => ['required', 'string', 'max:255'],
            'name_en'           => ['required', 'string', 'max:255'],
            'code'              => ['required', 'string', 'max:255'],
            'type'              => ['required', Rule::in(AccountType::values())],
            'recieve_payments'  => ['required', 'in:true,false'],
            'description'       => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
        // الاسم بالعربية
        'name_ar.required' => 'حقل الاسم بالعربية مطلوب.',
        'name_ar.string'   => 'حقل الاسم بالعربية يجب أن يكون نصاً.',
        'name_ar.max'      => 'حقل الاسم بالعربية يجب ألا يتجاوز 255 حرفاً.',

        // الاسم بالإنجليزية
        'name_en.required' => 'حقل الاسم بالإنجليزية مطلوب.',
        'name_en.string'   => 'حقل الاسم بالإنجليزية يجب أن يكون نصاً.',
        'name_en.max'      => 'حقل الاسم بالإنجليزية يجب ألا يتجاوز 255 حرفاً.',

        // الرمز
        'code.required' => 'حقل الرمز مطلوب.',
        'code.string'   => 'حقل الرمز يجب أن يكون نصاً.',
        'code.max'      => 'حقل الرمز يجب ألا يتجاوز 255 حرفاً.',

        // النوع
        'type.required' => 'حقل النوع مطلوب.',
        'type.in'       => 'نوع الحساب المحدد غير صالح.',

        // استقبال المدفوعات
        'receive_payments.required' => 'حقل استقبال المدفوعات مطلوب.',
        'receive_payments.in'       => 'قيمة استقبال المدفوعات غير صالحة.',

        // الوصف
        'description.string' => 'حقل الوصف يجب أن يكون نصاً.',
        'description.max'    => 'حقل الوصف يجب ألا يتجاوز 1000 حرف.',  
    ];
    }
}
