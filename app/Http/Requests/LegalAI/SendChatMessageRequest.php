<?php

namespace App\Http\Requests\LegalAI;

use App\Data\LegalAI\ChatMessageData;
use Illuminate\Foundation\Http\FormRequest;

class SendChatMessageRequest extends FormRequest
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
    | Validation Rules
    |--------------------------------------------------------------------------
    */
    public function rules(): array
    {
        return [
            'message' => 'required|string|max:20000',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Custom Messages
    |--------------------------------------------------------------------------
    */
    public function messages(): array
    {
        return [
            'message.required' => 'حقل الرسالة مطلوب.',
            'message.string' => 'يجب أن تكون الرسالة نصية.',
            'message.max' => 'يجب ألا تتجاوز الرسالة 20000 حرف.',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | تحويل بيانات الطلب التي تم التحقق منها إلى كائن DTO.
    |--------------------------------------------------------------------------
    */
    public function toDto(): ChatMessageData
    {
        return ChatMessageData::fromRequest($this);
    }
}
