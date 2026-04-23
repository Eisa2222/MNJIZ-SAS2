<?php

namespace App\Http\Requests\ElectronicServices\CustodyRequests\Return;

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
            'parent_request_id' => ['required', 'integer', 'exists:custody_requests,id'],
            'return_status'     => ['required',  Rule::in(CustodyReturnStatus::values())],
            'notes'             => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'parent_request_id.required'   => 'حقل طلب العهدة الأصلي مطلوب.',
            'parent_request_id.integer'    => 'حقل طلب العهدة الأصلي يجب أن يكون رقمًا صحيحًا.',
            'parent_request_id.exists'     => 'طلب العهدة الأصلي المحدد غير موجود.',

            'return_status.required'    => 'حقل سبب الارجاع  مطلوب.',
            'return_status.in'          => 'قيمة سبب الارجاع  غير صحيحة.',

            'notes.string'               => 'حقل الملاحظات يجب أن يكون نصًا.',
        ];
    }
}
