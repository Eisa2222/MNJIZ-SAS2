<?php

namespace App\Http\Requests\Qoyod\Customers;

use Illuminate\Foundation\Http\FormRequest;

class QCustomersRequest extends FormRequest
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
            'name'                    => ['required', 'string', 'max:255'],
            'organization'            => ['nullable', 'string', 'max:255'],
            'phone_number'            => ['nullable'],
            'secondary_phone_number'  => ['nullable'],
            'email'                   => ['nullable', 'email', 'max:255'],
            'status'                  => ['nullable', 'in:Active,Inactive'],
            'tax_number'              => ['nullable', 'regex:/^\d{15}$/'],
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
            'name.required'                   => 'حقل الاسم مطلوب.',
            'name.max'                        => 'اسم العميل يجب ألّا يتجاوز 255 حرفًا.',

            'organization.max'                => 'اسم المؤسسة يجب ألّا يتجاوز 255 حرفًا.',

            'phone_number.digits'             => 'رقم الهاتف يجب أن يتكوّن من 10 أرقام.',
            'secondary_phone_number.digits'   => 'رقم الهاتف الإضافي يجب أن يتكوّن من 10 أرقام.',

            'email.email'                     => 'صيغة البريد الإلكتروني غير صحيحة.',
            'email.max'                       => 'البريد الإلكتروني يجب ألّا يتجاوز 255 حرفًا.',

            'status.in'                       => 'الحالة يجب أن تكون Active أو Inactive فقط.',

            'tax_number.regex'                => 'الرقم الضريبي يجب أن يتكوّن من 15 رقمًا.',
        ];
    }
}
