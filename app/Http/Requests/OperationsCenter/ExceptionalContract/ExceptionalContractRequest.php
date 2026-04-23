<?php

namespace App\Http\Requests\OperationsCenter\ExceptionalContract;

use Illuminate\Foundation\Http\FormRequest;

class ExceptionalContractRequest extends FormRequest
{
    /*
    |--------------------------------------------------------------------------
    | Authorize
    |--------------------------------------------------------------------------
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
    public function rules(): array
    {
        return [
            'contract_name'     => 'required|string|max:255',
            'project_name'      => 'required|string|max:255',
            'customer_id'       => 'required|exists:customers,id',
            'employee_id'       => 'required|exists:employees,id',
            'scope_of_work'     => 'nullable|string',
            'reasons'           => 'nullable|string',
            'equivalent'        => 'nullable|string',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | messages
    |--------------------------------------------------------------------------
    */
    public function messages(): array
    {
        return [
            'contract_name.required'    => 'اسم العقد مطلوب.',
            'contract_name.string'      => 'اسم العقد يجب أن يكون نصاً.',
            'contract_name.max'         => 'اسم العقد يجب ألا يتجاوز 255 حرفًا.',

            'project_name.required'     => 'اسم المشروع مطلوب.',
            'project_name.string'       => 'اسم المشروع يجب أن يكون نصاً.',
            'project_name.max'          => 'اسم المشروع يجب ألا يتجاوز 255 حرفًا.',

            'customer_id.required'      => 'العميل مطلوب.',
            'customer_id.exists'        => 'العميل المحدد غير موجود.',

            'employee_id.required'      => 'الموظف مطلوب.',
            'employee_id.exists'        => 'الموظف المحدد غير موجود.',

            'scope_of_work.string'      => 'نطاق العمل يجب أن يكون نصاً.',

            'reasons.string'            => 'الأسباب يجب أن تكون نصاً.',

            'equivalent.string'         => 'القيمة المعادلة يجب أن تكون نصاً.',
        ];
    }
}
