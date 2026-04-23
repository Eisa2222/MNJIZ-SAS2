<?php

namespace App\Http\Requests\SelfServices;

use Illuminate\Foundation\Http\FormRequest;

class SelfServiceRequest extends FormRequest
{
    /*
    |--------------------------------------------------------------------------
    | authorize
    |--------------------------------------------------------------------------
    */
    public function authorize(): bool
    {
        return true;
    }

    
    /*
    |--------------------------------------------------------------------------
    | rules
    |--------------------------------------------------------------------------
    */
    public function rules(): array
    {
        return [
            'recipient' => ['required','string','min:3','max:255'],
        ];
    }


    
    /*
    |--------------------------------------------------------------------------
    | messages
    |--------------------------------------------------------------------------
    */
    public function messages(): array
    {
        return [
            'recipient.required'    => 'حقل الجهة الطالبة مطلوب.',
            'recipient.string'      => 'يجب أن يكون حقل الجهة الطالبة  نصاً.',
            'recipient.min'         => 'يجب ألا يقل حقل الجهة الطالبة  عن 3 أحرف.',
            'recipient.max'         => 'يجب ألا يزيد حقل الجهة الطالبة  عن 255 حرفاً.',
        ];
    }
}
