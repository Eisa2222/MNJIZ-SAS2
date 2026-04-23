<?php

namespace App\Http\Requests\Hr\Custody\Request\Assign;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id'     => ['required', 'integer', 'exists:employees,id'],
            'custody_item_id' => ['required', 'integer', 'exists:custody_items,id'],
            'notes'           => ['nullable', 'string'],

        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required'       => 'حقل الموظف مطلوب.',
            'employee_id.integer'        => 'حقل الموظف يجب أن يكون رقمًا صحيحًا.',
            'employee_id.exists'         => 'الموظف المحدد غير موجود.',

            'custody_item_id.required'   => 'حقل العهدة مطلوب.',
            'custody_item_id.integer'    => 'حقل العهدة يجب أن يكون رقمًا صحيحًا.',
            'custody_item_id.exists'     => 'العهدة المحدد غير موجود.',

            'notes.string'               => 'حقل الملاحظات يجب أن يكون نصًا.',
        ];
    }
}
