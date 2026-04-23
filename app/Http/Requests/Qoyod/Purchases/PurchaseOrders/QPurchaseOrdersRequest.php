<?php

namespace App\Http\Requests\Qoyod\Purchases\PurchaseOrders;

use Illuminate\Foundation\Http\FormRequest;

class QPurchaseOrdersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // الحقول الرئيسية
            'reference'        => ['required'],
            'contact_id'        => ['required'],
            'issue_date'        => ['required', 'date_format:Y-m-d'],
            'expiry_date'       => ['required', 'date_format:Y-m-d', 'after_or_equal:issue_date'],
            'status'            => ['required', 'in:Draft,Approved'],
            'inventory_id'      => ['required'],
            'notes'             => ['nullable', 'string'],
            'terms_conditions'  => ['nullable', 'string'],

            // مصفوفة البنود
            'line_items'                         => ['required', 'array', 'min:1'],
            'line_items.*.product_id'            => ['required'],
            'line_items.*.unit_type'             => ['nullable', 'integer'],
            'line_items.*.description'           => ['nullable', 'string', 'max:255'],
            'line_items.*.quantity'              => ['required', 'numeric', 'min:0.01'],
            'line_items.*.unit_price'            => ['required', 'numeric', 'min:0'],
            'line_items.*.discount'              => ['nullable', 'numeric', 'min:0'],
            'line_items.*.discount_type'         => ['nullable', 'in:percentage,amount'],
            'line_items.*.tax_percent'           => ['nullable', 'numeric', 'min:0'],

            // حقول مخصّصة إن وُجدت
            'custom_fields'                      => ['nullable', 'array'],
            'custom_fields.*.key'                => ['required_with:custom_fields', 'string'],
            'custom_fields.*.value'              => ['required_with:custom_fields'],
        ];
    }

    public function messages(): array
    {
        return [
            // الحقول الرئيسية
            'reference.required'           => 'حقل المرجع مطلوب.',
            'contact_id.required'           => 'حقل المورّد مطلوب.',
            'issue_date.required'           => 'تاريخ الإصدار مطلوب.',
            'issue_date.date_format'        => 'صيغة تاريخ الإصدار يجب أن تكون YYYY-MM-DD.',
            'expiry_date.required'          => 'تاريخ الاستحقاق مطلوب.',
            'expiry_date.date_format'       => 'صيغة تاريخ الاستحقاق يجب أن تكون YYYY-MM-DD.',
            'expiry_date.after_or_equal'    => 'تاريخ الاستحقاق يجب أن يكون مساوٍ أو بعد تاريخ الإصدار.',
            'status.required'               => 'حقل الحالة مطلوب.',
            'status.in'                     => 'الحالة يجب أن تكون Draft أو Approved.',
            'inventory_id.required'         => 'حقل المخزون مطلوب.',

            // البنود
            'line_items.required'               => 'يجب إضافة بند واحد على الأقل.',
            'line_items.array'                  => 'حقل البنود يجب أن يكون مصفوفة.',
            'line_items.*.product_id.required'  => 'حقل المنتج مطلوب في كل بند.',
            'line_items.*.quantity.required'    => 'الكمية مطلوبة في كل بند.',
            'line_items.*.quantity.numeric'     => 'الكمية يجب أن تكون رقمًا.',
            'line_items.*.quantity.min'         => 'الكمية يجب أن تكون أكبر من صفر.',
            'line_items.*.unit_price.required'  => 'سعر الوحدة مطلوب في كل بند.',
            'line_items.*.unit_price.numeric'   => 'سعر الوحدة يجب أن يكون رقمًا.',
            'line_items.*.discount_type.in'     => 'نوع الخصم يجب أن يكون percentage أو amount.',
            'line_items.*.tax_percent.numeric'  => 'نسبة الضريبة يجب أن تكون رقمًا.',

            // الحقول المخصّصة
            'custom_fields.array'                   => 'حقل الحقول المخصّصة يجب أن يكون مصفوفة.',
            'custom_fields.*.key.required_with'     => 'مفتاح الحقل المخصّص مطلوب.',
            'custom_fields.*.value.required_with'   => 'قيمة الحقل المخصّص مطلوبة.',
        ];
    }
}
