<?php

namespace App\Http\Requests\Hr\Payrolls;

use Illuminate\Foundation\Http\FormRequest;

class GenerateWpsRequest extends FormRequest
{
    /*
    |============================================================================
    | Authorize
    |============================================================================
    */
    public function authorize(): bool
    {
        return true;
    }


    /*
    |============================================================================
    | Rules
    |============================================================================
    */
    public function rules(): array
    {
        return [
            'month' => ['required', 'numeric', 'between:1,12'],
            'year'  => ['required', 'digits:4', 'integer', 'min:1900', 'max:' . now()->year],
        ];
    }


    /*
    |============================================================================
    | Messages
    |============================================================================
    */
    public function messages()
    {
        return [

            // رسائل حقل الشهر
            'month.required' => 'حقل الشهر مطلوب.',
            'month.numeric'  => 'حقل الشهر يجب أن يكون رقمًا.',
            'month.between'  => 'حقل الشهر يجب أن يكون بين 1 و 12.',

            // رسائل حقل السنة
            'year.required'  => 'حقل السنة مطلوب.',
            'year.digits'    => 'حقل السنة يجب أن يتكون من 4 أرقام.',
            'year.integer'   => 'حقل السنة يجب أن يكون عددًا صحيحًا.',
            'year.min'       => 'حقل السنة لا يمكن أن يقل عن 1900.',
            'year.max'       => 'حقل السنة لا يمكن أن يتجاوز ' . now()->year . '.',
        ];
    }
}
