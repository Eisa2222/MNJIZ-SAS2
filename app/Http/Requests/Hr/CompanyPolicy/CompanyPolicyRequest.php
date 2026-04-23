<?php

namespace App\Http\Requests\Hr\CompanyPolicy;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyPolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name.*'                => ['required', 'string', 'max:255'],
            'file.*'                => ['required', 'file', 'mimetypes:application/pdf', 'max:10240'], // 10MB
            'is_mandatory.*'        => ['nullable', Rule::in(['0', '1'])],
        ];
    }

    public function messages(): array
    {
        return [
            'name.*.required'       => 'الاسم مطلوب',
            'file.*.required'       => 'المرفق مطلوب',
            'file.*.mimetypes'      => 'يجب أن يكون الملف PDF',
            'file.*.max'            => 'الحجم الأقصى 10 ميغابايت',
        ];
    }
}
