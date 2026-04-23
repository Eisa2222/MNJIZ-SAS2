<?php

namespace App\Http\Requests\Hr\Custody\Request\Return;

use App\Enums\ElectronicServices\Custody\Requests\CustodyReturnStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id'       => ['required', 'integer', 'exists:employees,id'],
            'parent_request_id' => ['required', 'integer', 'exists:custody_requests,id'],
            'return_status'     => ['required',  Rule::in(CustodyReturnStatus::values())],
            'notes'             => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required'       => 'حقل الموظف مطلوب.',
            'employee_id.integer'        => 'حقل الموظف يجب أن يكون رقمًا صحيحًا.',
            'employee_id.exists'         => 'الموظف المحدد غير موجود.',

            'parent_request_id.required'   => 'حقل طلب العهدة الأصلي مطلوب.',
            'parent_request_id.integer'    => 'حقل طلب العهدة الأصلي يجب أن يكون رقمًا صحيحًا.',
            'parent_request_id.exists'     => 'طلب العهدة الأصلي المحدد غير موجود.',

            'return_status.required'    => 'حقل سبب الارجاع  مطلوب.',
            'return_status.in'          => 'قيمة سبب الارجاع  غير صحيحة.',


            'notes.string'               => 'حقل الملاحظات يجب أن يكون نصًا.',
        ];
    }
}
