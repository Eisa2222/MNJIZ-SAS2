<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    | Determine if the user is authorized to make this request.
    */

    public function authorize(): bool
    {
        return auth()->check();
    }

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    | Get the validation rules that apply to the request.
    */

    public function rules(): array
    {
        return [
            'receiver_id' => 'required|exists:users,id|different:' . auth()->id(),
            'message' => 'nullable|string|max:10000|required_without:attachment',
            'attachment' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx,txt,zip,rar|required_without:message'
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Custom Messages
    |--------------------------------------------------------------------------
    | Get custom messages for validator errors.
    */

    public function messages(): array
    {
        return [
            'receiver_id.required' => 'يجب تحديد المستقبل',
            'receiver_id.exists' => 'المستقبل المحدد غير موجود',
            'receiver_id.different' => 'لا يمكن إرسال رسالة لنفسك',
            'message.required_without' => 'يجب كتابة رسالة أو إرفاق ملف',
            'message.max' => 'الرسالة طويلة جداً',
            'attachment.required_without' => 'يجب كتابة رسالة أو إرفاق ملف',
            'attachment.max' => 'حجم الملف كبير جداً (الحد الأقصى 10 ميجابايت)',
            'attachment.mimes' => 'نوع الملف غير مدعوم'
        ];
    }
}
