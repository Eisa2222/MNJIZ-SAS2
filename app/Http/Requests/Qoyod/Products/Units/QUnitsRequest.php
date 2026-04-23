<?php

namespace App\Http\Requests\Qoyod\Products\Units;

use Illuminate\Foundation\Http\FormRequest;

class QUnitsRequest extends FormRequest
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
            'unit_name'              => ['required', 'string', 'max:255'],
            'unit_representation'    => ['required', 'string', 'max:255'],
        ];
    }


    /*
    |============================================================================
    | Messages
    |============================================================================
    */
    public function messages(): array
    {
        return [
            // unit_name
            'unit_name.required'                => 'حقل الوحدة مطلوب.',
            'unit_name.string'                  => 'حقل الوحدة يجب أن يكون نصًا.',
            'unit_name.max'                     => 'الوحدة لا يمكن أن يتجاوز 255 حرفًا.',

            // unit_representation
            'unit_representation.required'      => 'حقل طريقة العرض مطلوب.',
            'unit_representation.string'        => 'حقل طريقة العرض يجب أن يكون نصًا.',
            'unit_representation.max'           => 'طريقة العرض لا يمكن أن يتجاوز 255 حرفًا.',
        ];
    }
}
