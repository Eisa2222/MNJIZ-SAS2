<?php

namespace App\Http\Requests\GeneralSetting\SettingsSocial;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingsSocialRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Rules
    |--------------------------------------------------------------------------
    */
    public function rules()
    {
        return [
            'api_key'             => 'required|string',
            'api_secret'          => 'required|string',
            'access_token'        => 'required|string',
            'access_token_secret' => 'required|string',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Messages
    |--------------------------------------------------------------------------
    */
    public function messages()
    {
        return [
            'api_key.required'             => 'حقل مفتاح API مطلوب.',
            'api_key.string'               => 'حقل مفتاح API يجب أن يكون نصاً.',
            'api_secret.required'          => 'حقل سر API مطلوب.',
            'api_secret.string'            => 'حقل سر API يجب أن يكون نصاً.',
            'access_token.required'        => 'حقل رمز الوصول مطلوب.',
            'access_token.string'          => 'حقل رمز الوصول يجب أن يكون نصاً.',
            'access_token_secret.required' => 'حقل سرّ رمز الوصول مطلوب.',
            'access_token_secret.string'   => 'حقل سرّ رمز الوصول يجب أن يكون نصاً.',
        ];
    }
}
