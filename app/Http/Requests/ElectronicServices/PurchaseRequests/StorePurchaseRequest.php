<?php

namespace App\Http\Requests\ElectronicServices\PurchaseRequests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
{
    /*
    |--------------------------------------------------------------------------
    | Authorization
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
            'item_name'            => ['required', 'string', 'max:255'],
            'purchase_category_id' => ['required', 'integer', 'exists:settings_purchase_categories,id'],
            'item_quantity'        => ['nullable', 'integer', 'min:1', 'max:100'],
            'item_description'     => ['nullable', 'string'],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */
    public function attributes(): array
    {
        return [
            'item_name'            => 'الطلب',
            'purchase_category_id' => 'التصنيف',
            'item_quantity'        => 'الكمية',
            'item_description'     => 'الملاحظة',
        ];
    }

    
    /*
    |--------------------------------------------------------------------------
    | Messages
    |--------------------------------------------------------------------------
    */
    public function messages(): array
    {
        return [
            // item_name
            'item_name.required' => 'حقل :attribute مطلوب.',
            'item_name.string'   => 'حقل :attribute يجب أن يكون نصاً.',
            'item_name.max'      => 'حقل :attribute يجب ألا يتجاوز :max حرفاً.',

            // purchase_category_id
            'purchase_category_id.required' => 'حقل :attribute مطلوب.',
            'purchase_category_id.integer'  => 'حقل :attribute يجب أن يكون رقماً صحيحاً.',
            'purchase_category_id.exists'   => 'فئة الشراء المحددة غير موجودة.',

            // item_quantity
            'item_quantity.integer' => 'حقل :attribute يجب أن يكون رقماً صحيحاً.',
            'item_quantity.min'     => 'حقل :attribute يجب أن يكون على الأقل :min.',
            'item_quantity.max'     => 'حقل :attribute  يجب الا يتجاوز :max.',

            // item_description
            'item_description.string' => 'حقل :attribute يجب أن يكون نصاً.',
        ];
    }
}
