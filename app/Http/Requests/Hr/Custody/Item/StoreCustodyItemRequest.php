<?php

namespace App\Http\Requests\Hr\Custody\Item;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustodyItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                  => ['required', 'string', 'max:255'],
            'price'                 => ['nullable', 'numeric', 'min:0'],
            'asset_category_id'     => ['required', 'integer', 'exists:settings_asset_categories,id'],
            'storage_location_id'   => ['required', 'integer', 'exists:settings_storage_locations,id'],
            'description'           => ['nullable', 'string'],

            'serial_numbers'        => ['required', 'array', 'min:1'],
            'serial_numbers.*'      => ['required', 'string', 'max:255', 'distinct', 'unique:custody_items,serial_number'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                 => 'حقل الاسم مطلوب.',
            'name.string'                   => 'حقل الاسم يجب أن يكون نصاً.',
            'name.max'                      => 'حقل الاسم يجب ألا يتجاوز 255 حرفاً.',

            'price.numeric'                 => 'السعر يجب أن يكون رقماً.',
            'price.min'                     => 'السعر يجب أن يكون أكبر من أو يساوي صفر.',

            'asset_category_id.required'    => 'حقل تصنيف الأصل مطلوب.',
            'asset_category_id.integer'     => 'تصنيف الأصل يجب أن يكون رقماً صحيحاً.',
            'asset_category_id.exists'      => 'تصنيف الأصل المحدد غير موجود.',

            'storage_location_id.required'  => 'حقل مرجعية الاصل مطلوبة.',
            'storage_location_id.integer'   => 'مرجعية الاصل يجب أن يكون رقماً صحيحاً.',
            'storage_location_id.exists'    => 'مرجعية الاصل المحدد غير موجود.',


            'serial_numbers.required'       => 'يجب إدخال رقم تسلسلي واحد على الأقل.',
            'serial_numbers.array'          => 'الأرقام التسلسلية يجب أن تكون في شكل مصفوفة.',
            'serial_numbers.min'            => 'يجب إدخال رقم تسلسلي واحد على الأقل.',

            'serial_numbers.*.required'     => 'الرقم التسلسلي مطلوب.',
            'serial_numbers.*.string'       => 'الرقم التسلسلي يجب أن يكون نصاً.',
            'serial_numbers.*.max'          => 'الرقم التسلسلي يجب ألا يتجاوز 255 حرفاً.',
            'serial_numbers.*.distinct'     => 'الأرقام التسلسلية يجب أن تكون فريدة.',
            'serial_numbers.*.unique'       => 'هذا الرقم التسلسلي مستخدم بالفعل.',

        ];
    }
}
