<?php

namespace App\Http\Requests\Hr\Custody\Item;

use App\Enums\Hr\Custody\Item\CustodyUs;
use App\Enums\Hr\Custody\Item\CustodyUseStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustodyItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $itemId = $this->route('item')->id;

        return [
            'name'                  => ['required', 'string', 'max:255'],
            'serial_number'         => ['required', 'string', 'max:255', Rule::unique('custody_items', 'serial_number')->ignore($itemId)],
            'price'                 => ['nullable', 'numeric', 'min:0'],
            'asset_category_id'     => ['required', 'integer', 'exists:settings_asset_categories,id'],
            'storage_location_id'   => ['required', 'integer', 'exists:settings_storage_locations,id'],
            'description'           => ['nullable', 'string'],
            'use_status'            => ['required', 'string', Rule::in(CustodyUseStatus::values())],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                 => 'حقل الاسم مطلوب.',
            'name.string'                   => 'حقل الاسم يجب أن يكون نصاً.',
            'name.max'                      => 'حقل الاسم يجب ألا يتجاوز 255 حرفاً.',

            'serial_number.required'        => 'حقل الرقم التسلسلي مطلوب.',
            'serial_number.string'          => 'الرقم التسلسلي يجب أن يكون نصاً.',
            'serial_number.max'             => 'الرقم التسلسلي يجب ألا يتجاوز 255 حرفاً.',
            'serial_number.unique'          => 'الرقم التسلسلي مستخدم بالفعل.',

            'price.numeric'                 => 'السعر يجب أن يكون رقماً.',
            'price.min'                     => 'السعر يجب أن يكون أكبر من أو يساوي صفر.',

            'asset_category_id.required'    => 'حقل تصنيف الأصل مطلوب.',
            'asset_category_id.integer'     => 'تصنيف الأصل يجب أن يكون رقماً صحيحاً.',
            'asset_category_id.exists'      => 'تصنيف الأصل المحدد غير موجود.',

            'storage_location_id.required'  => 'حقل مرجعية الاصل مطلوبة.',
            'storage_location_id.integer'   => 'مرجعية الاصل يجب أن يكون رقماً صحيحاً.',
            'storage_location_id.exists'    => 'مرجعية الاصل المحدد غير موجود.',

            'use_status.required'               => 'حقل حالة الاستخدام مطلوب.',
            'use_status.in'                     => 'حقل حالة الاستخدام غير صالح.',
        ];
    }
}
