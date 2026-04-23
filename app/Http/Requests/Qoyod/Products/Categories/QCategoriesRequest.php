<?php

namespace App\Http\Requests\Qoyod\Products\Categories;

use Illuminate\Foundation\Http\FormRequest;

class QCategoriesRequest extends FormRequest
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
            'name'              => ['required', 'string', 'max:255'],
            'description'       => ['required', 'string', 'max:255'],
            'parent_id'         => ['nullable', 'integer'],
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
        'name.required'        => 'حقل اسم الصنف مطلوب.',
        'name.string'          => 'يجب أن يكون اسم الصنف نصاً.',
        'name.max'             => 'يجب ألا يتجاوز اسم الصنف 255 حرفاً.',
        
        'description.required' => 'حقل الوصف مطلوب.',
        'description.string'   => 'يجب أن يكون الوصف نصاً.',
        'description.max'      => 'يجب ألا يتجاوز الوصف 255 حرفاً.',
        
        'parent_id.integer'    => 'يجب أن يكون الصنف الاساس  واحدا من التصنيفات الموجودة في القائمة.',
    ];
    }
}
