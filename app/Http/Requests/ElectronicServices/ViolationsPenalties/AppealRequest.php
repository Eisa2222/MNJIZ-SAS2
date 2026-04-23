<?php

namespace App\Http\Requests\ElectronicServices\ViolationsPenalties;

use Illuminate\Foundation\Http\FormRequest;

class AppealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'appeal_reason' => 'required|string|min:10',
        ];
    }


    public function messages()
    {
        return [
            'appeal_reason.required' => 'حقل سبب التظلم  مطلوب.',
            'appeal_reason.min'      => 'يجب أن يكون سبب التظلم 10 حروف على الأقل.',
        ];
    }
}
