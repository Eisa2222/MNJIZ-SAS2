<?php

namespace App\Http\Requests\Hr\CompanyPolicy;

use Illuminate\Foundation\Http\FormRequest;

class CompanyPolicyUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'file'          => ['nullable', 'file', 'mimetypes:application/pdf', 'max:10240'],
            'is_mandatory'  => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'الاسم مطلوب',
            'name.max'          => 'الاسم لا يجب أن يتجاوز 255 حرف',
            'file.mimetypes'    => 'يجب أن يكون الملف من نوع PDF',
            'file.max'          => 'حجم الملف لا يجب أن يتجاوز 10 ميغابايت',
        ];
    }
}
