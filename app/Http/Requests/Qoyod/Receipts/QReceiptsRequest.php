<?php

namespace App\Http\Requests\Qoyod\Receipts;

use Illuminate\Foundation\Http\FormRequest;

class QReceiptsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // الحقول الرئيسية
            'reference'     => ['required'],
            'customer_id'   => ['integer'],
            'supplier_id'   => ['integer'],
            'account_id'    => ['required', 'integer'],
            'kind'          => ['required'],
            'amount'        => ['required'],
            'date'          => ['required', 'date_format:Y-m-d'],
            'description'   => ['nullable'],

        ];
    }

    public function messages(): array
    {
        return [];
    }
}
