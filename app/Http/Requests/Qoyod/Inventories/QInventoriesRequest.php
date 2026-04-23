<?php

namespace App\Http\Requests\Qoyod\Inventories;

use Illuminate\Foundation\Http\FormRequest;

class QInventoriesRequest extends FormRequest
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
            'name'                => ['required', 'string', 'min:3', 'max:150'],
            'ar_name'             => ['required', 'string', 'min:3', 'max:150'],
            'account_id'          => ['required', 'integer'],
            
            'shipping_address'    => ['nullable', 'string', 'max:255'],
            'shipping_city'       => ['nullable', 'string', 'max:100'],
            'shipping_state'      => ['nullable', 'string', 'max:100'],
            'shipping_zip'        => ['nullable', 'string', 'max:20'],
            'shipping_country'    => ['nullable', 'string', 'max:100'],
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
            // Name
            'name.required'              => 'حقل الاسم بالإنجليزية مطلوب.',
            'name.string'                => 'حقل الاسم بالإنجليزية يجب أن يكون نصًّا.',
            'name.min'                   => 'يجب أن لا تقل حروف الاسم بالإنجليزية عن 3 حروف.',
            'name.max'                   => 'يجب أن لا يزيد طول الاسم بالإنجليزية عن 150 حرفًا.',

            // Name Arabic
            'ar_name.required'           => 'حقل الاسم بالعربية مطلوب.',
            'ar_name.string'             => 'حقل الاسم بالعربية يجب أن يكون نصًّا.',
            'ar_name.min'                => 'يجب أن لا تقل حروف الاسم بالعربية عن 3 حروف.',
            'ar_name.max'                => 'يجب أن لا يزيد طول الاسم بالعربية عن 150 حرفًا.',

            // Account
            'account_id.required'        => 'حقل حساب المخزون مطلوب.',
            'account_id.integer'         => 'حقل حساب المخزون يجب أن يكون من الحسابات المحددة.',

            // Address fields
            'shipping_address.string'    => 'حقل العنوان يجب أن يكون نصًّا.',
            'shipping_address.max'       => 'يجب أن لا يزيد طول العنوان عن 255 حرفًا.',
            'shipping_city.string'       => 'حقل المدينة يجب أن يكون نصًّا.',
            'shipping_city.max'          => 'يجب أن لا يزيد طول المدينة عن 100 حرف.',
            'shipping_state.string'      => 'حقل المنطقة يجب أن يكون نصًّا.',
            'shipping_state.max'         => 'يجب أن لا يزيد طول المنطقة عن 100 حرف.',
            'shipping_zip.string'        => 'حقل الرمز البريدي يجب أن يكون نصًّا.',
            'shipping_zip.max'           => 'يجب أن لا يزيد طول الرمز البريدي عن 20 حرف.',
            'shipping_country.string'    => 'حقل الدولة يجب أن يكون نصًّا.',
            'shipping_country.max'       => 'يجب أن لا يزيد طول الدولة عن 100 حرف.',
        ];
    }

    /*
    |============================================================================
    | Prepare Data for API
    |============================================================================
    */
    public function getFormattedData(): array
    {
        $validated = $this->validated();
        
        // تجهيز البيانات بالتنسيق المطلوب للـ API
        $data = [
            'inventory' => [
                'name' => $validated['name'],
                'ar_name' => $validated['ar_name'],
                'account_id' => (string) $validated['account_id'], // تحويل إلى string إذا كان مطلوب
            ]
        ];

        // إضافة العنوان إذا كان موجود
        $addressFields = [
            'shipping_address',
            'shipping_city', 
            'shipping_state',
            'shipping_zip',
            'shipping_country'
        ];

        $address = [];
        foreach ($addressFields as $field) {
            if (!empty($validated[$field])) {
                $address[$field] = $validated[$field];
            }
        }

        // إضافة العنوان إلى البيانات إذا كان هناك أي حقل مُملأ
        if (!empty($address)) {
            $data['inventory']['address'] = $address;
        }

        return $data;
    }
}