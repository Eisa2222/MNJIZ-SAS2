<?php

namespace App\Http\Requests\Qoyod\Vendors;

use Illuminate\Foundation\Http\FormRequest;

class QVendorsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'organization'  => ['nullable', 'string', 'max:255'],
            'phone_number'  => ['nullable', 'string', 'max:20'], 
            'email'         => ['nullable', 'email',  'max:255'],
            'tax_number'    => ['nullable', 'string', 'max:150', ],
        ];
    }

    public function messages(): array
    {
        return [
            // حقل name
            'name.required'         => 'حقل اسم المورد مطلوب.',
            'name.string'           => 'حقل اسم المورد يجب أن يكون نصاً.',
            'name.max'              => 'حقل اسم المورد لا يجوز أن يزيد عن 255 حرفاً.',

            // حقل organization
            'organization.string'   => 'حقل اسم المنشأة يجب أن يكون نصاً.',
            'organization.max'      => 'حقل اسم المنشأة لا يجوز أن يزيد عن 255 حرفاً.',

            // حقل phone_number
            'phone_number.string'   => 'حقل رقم الاتصال يجب أن يكون نصاً.',
            'phone_number.max'      => 'حقل رقم الاتصال لا يجوز أن يزيد عن 20 حرفاً.',

            // حقل email
            'email.email'           => 'يجب أن يكون حقل البريد الإلكتروني عنوان بريد صالحاً.',
            'email.max'             => 'حقل البريد الإلكتروني لا يجوز أن يزيد عن 255 حرفاً.',

            // حقل tax_number
            'tax_number.string'     => 'حقل الرقم الضريبي يجب أن يكون نصاً.',
            'tax_number.max'        => 'حقل الرقم الضريبي لا يجوز أن يزيد عن 150 حرفاً.',

        ];
    }

}
