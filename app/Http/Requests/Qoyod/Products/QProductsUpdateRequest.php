<?php

namespace App\Http\Requests\Qoyod\Products;

use Illuminate\Foundation\Http\FormRequest;

class QProductsUpdateRequest extends FormRequest
{
    /*
    |============================================================================
    | Authorize
    |============================================================================
    */
    public function authorize(): bool
    {
        return true;
    }


    /*
    |============================================================================
    | Rules
    |============================================================================
    */
    public function rules(): array
    {

        return [
            'sku'                    => ['required', 'string', 'max:150'],
            'barcode'                => ['nullable', 'string', 'max:150'],
            'name_ar'                => ['required', 'string', 'min:3', 'max:150'],
            'name_en'                => ['required', 'string', 'min:3', 'max:150'],
            'description'            => ['nullable', 'string'],
            'category_id'            => ['required', 'integer'],
            'product_unit_type_id'   => ['required', 'integer'],
            'buying_price'           => ['required_if:type,Expense', 'nullable', 'numeric', 'min:0'],
            'expense_account_id'     => ['required_if:type,Expense', 'nullable', 'integer' ],
            'selling_price'          => ['required_if:type,Service', 'nullable', 'numeric', 'min:0'],
            'sales_account_id'       => ['required_if:type,Service', 'nullable', 'integer'],
            'tax_id'                 => ['required', 'integer'],
            'special_tax_reason_id'  => [
                'required_if:tax_id,2,3',
                'nullable',
                'integer',
                'in:1,2,3,4,5,6,7,8,9,10,11,12,13,14'
            ],
        ];
    }


    /*
    |============================================================================
    | Messages
    |============================================================================
    */
    public function messages()
    {
        return [
            // SKU
            'sku.required'                  => 'حقل الرقم التسلسلي (SKU) مطلوب.',
            'sku.string'                    => 'حقل الرقم التسلسلي يجب أن يكون نصًّا.',
            'sku.max'                       => 'يجب أن لا يزيد طول الرقم التسلسلي عن 150 حرفًا.',

            // Barcode
            'barcode.string'                => 'حقل الباركود يجب أن يكون نصًّا.',
            'barcode.max'                   => 'يجب أن لا يزيد طول الباركود عن 150 حرفًا.',

            // Name Arabic
            'name_ar.required'              => 'حقل الاسم بالعربية مطلوب.',
            'name_ar.string'                => 'حقل الاسم بالعربية يجب أن يكون نصًّا.',
            'name_ar.min'                   => 'يجب أن لا تقل حروف الاسم بالعربية عن 3 حروف.',
            'name_ar.max'                   => 'يجب أن لا يزيد طول الاسم بالعربية عن 150 حرفًا.',

            // Name English
            'name_en.required'              => 'حقل الاسم بالإنجليزية مطلوب.',
            'name_en.string'                => 'حقل الاسم بالإنجليزية يجب أن يكون نصًّا.',
            'name_en.min'                   => 'يجب أن لا تقل حروف الاسم بالإنجليزية عن 3 حروف.',
            'name_en.max'                   => 'يجب أن لا يزيد طول الاسم بالإنجليزية عن 150 حرفًا.',

            // Description
            'description.string'            => 'حقل الوصف يجب أن يكون نصًّا.',

            // Category
            'category_id.required'          => 'حقل الصنف مطلوب.',
            'category_id.integer'           => 'حقل الصنف يجب أن يكون رقميًا (معرّف صالح).',

            // Unit Type
            'product_unit_type_id.required' => 'حقل وحدة القياس مطلوب.',
            'product_unit_type_id.integer'  => 'حقل وحدة القياس يجب أن يكون رقميًا (معرّف صالح).',

            // Buying Price
            'buying_price.required_if'      => 'حقل سعر الشراء مطلوب لأن نوع المنتج “مصروف”.',
            'buying_price.numeric'          => 'حقل سعر الشراء يجب أن يكون رقمًا.',
            'buying_price.min'              => 'حقل سعر الشراء لا يمكن أن يكون أقلّ من 0.',

            // Expense Account
            'expense_account_id.required_if' => 'حقل حساب المصروفات مطلوب لأن نوع المنتج “مصروف”.',
            'expense_account_id.integer'    => 'حقل حساب المصروفات يجب أن يكون رقميًا (معرّف صالح).',

            // Selling Price
            'selling_price.required_if'     => 'حقل سعر البيع مطلوب لأن نوع المنتج “خدمة”.',
            'selling_price.numeric'         => 'حقل سعر البيع يجب أن يكون رقمًا.',
            'selling_price.min'             => 'حقل سعر البيع لا يمكن أن يكون أقلّ من 0.',

            // Sales Account
            'sales_account_id.required_if'  => 'حقل حساب المبيعات مطلوب لأن نوع المنتج “خدمة”.',
            'sales_account_id.integer'      => 'حقل حساب المبيعات يجب أن يكون رقميًا (معرّف صالح).',

            // Tax
            'tax_id.required'               => 'حقل الضريبة مطلوب.',
            'tax_id.integer'                => 'حقل الضريبة يجب أن يكون رقميًا (معرّف صالح).',

            // Special Tax Reason
            'special_tax_reason_id.required_if' => 'حقل سبب الضريبة الخاصة مطلوب لأن الضريبة المحددة Zero أو Exempt.',
            'special_tax_reason_id.integer'     => 'حقل سبب الضريبة الخاصة يجب أن يكون رقميًا (معرّف صالح).',
            'special_tax_reason_id.in'          => 'سبب الضريبة الخاصة غير صالح. يرجى الاختيار من القائمة.',
        ];
    }
}
