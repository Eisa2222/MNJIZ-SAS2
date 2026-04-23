<?php

namespace App\Http\Requests\Hr\ViolationsPenalties;

use Illuminate\Foundation\Http\FormRequest;

class RespondToAppealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'        => 'required|in:approved,rejected',
            'response'      => 'required|string|min:10',
        ];
    }

    public function messages()
    {
        return [
            'status.required'       => 'يجب تحديد قرار التظلم',
            'status.in'             => 'قرار التظلم يجب أن يكون إما قبول أو رفض',
            'response.required'     => 'نص الرد على التظلم مطلوب',
            'response.min'          => 'يجب أن يحتوي الرد على الأقل على :min أحرف',
        ];
    }
}
