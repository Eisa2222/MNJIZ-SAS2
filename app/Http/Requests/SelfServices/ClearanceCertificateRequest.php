<?php

namespace App\Http\Requests\SelfServices;

use Illuminate\Foundation\Http\FormRequest;

class ClearanceCertificateRequest extends FormRequest
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
            'reason'    => ['required','string','max:255'],
            'notes'     => ['nullable', 'string', 'max:65535'],
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
            'reason.required' => 'حقل السبب مطلوب.',
            'reason.string'   => 'يجب أن يكون السبب نصاً.',
            'reason.max'      => 'يجب أن لا يزيد طول السبب عن 255 حرفاً.',
        ];
    }
}
