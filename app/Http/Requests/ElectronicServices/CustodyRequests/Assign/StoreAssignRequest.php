<?php

namespace App\Http\Requests\ElectronicServices\CustodyRequests\Assign;

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
            'custody_item_id' => ['required', 'integer', 'exists:custody_items,id'],
            'notes'           => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'custody_item_id.required'   => 'حقل العهدة مطلوب.',
            'custody_item_id.integer'    => 'حقل العهدة يجب أن يكون رقمًا صحيحًا.',
            'custody_item_id.exists'     => 'العهدة المحدد غير موجود.',

            'notes.string'               => 'حقل الملاحظات يجب أن يكون نصًا.',
        ];
    }
}
