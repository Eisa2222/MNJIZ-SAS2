<?php

namespace App\Http\Requests\Qoyod\Purchases\BillPayments;

use Illuminate\Foundation\Http\FormRequest;

class QBillPaymentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'bill_id'       => 'required|integer|min:1',
            'reference'     => 'required|string|min:3|max:50|regex:/^[a-zA-Z0-9\-_]+$/',
            'account_id'    => 'required|integer|min:1',
            'date'          => 'required|date',
            'amount'        => 'required|numeric|min:0.01|max:999999.99|regex:/^\d+(\.\d{1,2})?$/',
            'description'   => 'nullable|string|max:500'
        ];
    }



    public function messages(): array
    {
        return [
            // bill_id
            'bill_id.required'      => 'معرف الفاتورة مطلوب',
            'bill_id.integer'       => 'معرف الفاتورة يجب أن يكون رقماً صحيحاً',
            'bill_id.min'           => 'معرف الفاتورة غير صحيح',

            // reference
            'reference.required'    => 'رقم السند مطلوب',
            'reference.string'      => 'رقم السند يجب أن يكون نصاً',
            'reference.min'         => 'رقم السند يجب أن يكون 3 أحرف على الأقل',
            'reference.max'         => 'رقم السند لا يمكن أن يتجاوز 50 حرف',
            'reference.regex'       => 'رقم السند يمكن أن يحتوي على أحرف وأرقام و (-) و (_) فقط',

            // account_id
            'account_id.required'   => 'الحساب مطلوب',
            'account_id.integer'    => 'معرف الحساب يجب أن يكون رقماً صحيحاً',
            'account_id.min'        => 'معرف الحساب غير صحيح',

            // date
            'date.required'         => 'تاريخ الدفع مطلوب',
            'date.date'             => 'تاريخ الدفع غير صحيح',

            // amount
            'amount.required'       => 'المبلغ مطلوب',
            'amount.numeric'        => 'المبلغ يجب أن يكون رقماً',
            'amount.min'            => 'المبلغ يجب أن يكون أكبر من صفر',
            'amount.max'            => 'المبلغ كبير جداً (الحد الأقصى 999,999.99)',
            'amount.regex'          => 'المبلغ يجب أن يحتوي على رقمين عشريين كحد أقصى',

            // description
            'description.string'    => 'الوصف يجب أن يكون نصاً',
            'description.max'       => 'الوصف لا يمكن أن يتجاوز 500 حرف'
        ];
    }
}
