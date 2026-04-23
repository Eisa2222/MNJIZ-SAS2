<?php

namespace App\Http\Requests\LegalAffair\PowerOfAttorney;

use Illuminate\Foundation\Http\FormRequest;

class StorePowerAttorneyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'power_name'        => 'required|string|min:3|max:100',
            'power_number'      => 'required|string|min:5|max:50|unique:power_of_attorneys,power_number',
            'agents'            => 'required|array',
            'agents.*'          => 'exists:employees,id',
            'customers'         => 'required|array',
            'customers.*'       => 'exists:customers,id',
            'date_issued'       => 'required|date',
            'date_expiry'       => 'nullable|date',
            'file_attachment'   => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048', // 2MB
            'notes'             => 'nullable',
        ];
    }



    public function messages(): array
    {
        return [
            'power_name.required' => 'حقل اسم الوكالة مطلوب.',
            'power_name.string' => 'يجب أن يكون اسم الوكالة نصًا صحيحًا.',
            'power_name.min' => 'اسم الوكالة يجب أن يتكون من 3 أحرف على الأقل.',
            'power_name.max' => 'اسم الوكالة يجب ألا يتجاوز 100 حرف.',

            'power_number.required' => 'حقل رقم الوكالة مطلوب.',
            'power_number.string' => 'يجب أن يكون رقم الوكالة نصًا صحيحًا.',
            'power_number.min' => 'رقم الوكالة يجب أن يتكون من 5 أحرف على الأقل.',
            'power_number.max' => 'رقم الوكالة يجب ألا يتجاوز 50 حرفًا.',
            'power_number.unique' => 'رقم الوكالة موجود بالفعل.',

            'agents.required' => 'يجب اختيار وكيل واحد على الأقل.',
            'agents.array' => 'يجب أن تكون قائمة الوكلاء مصفوفة.',
            'agents.*.exists' => 'الوكيل المختار غير موجود.',

            'customers.required' => 'يجب اختيار عميل واحد على الأقل.',
            'customers.array' => 'يجب أن تكون قائمة العملاء مصفوفة.',
            'customers.*.exists' => 'العميل المختار غير موجود.',

            'date_issued.required' => 'حقل تاريخ إصدار الوكالة مطلوب.',
            'date_issued.date' => 'تاريخ إصدار الوكالة غير صالح.',
            'date_issued.date_format' => 'تاريخ إصدار الوكالة يجب أن يكون بتنسيق السنة-الشهر-اليوم (Y-m-d).',

            'date_expiry.date' => 'تاريخ انتهاء الوكالة غير صالح.',
            'date_expiry.date_format' => 'تاريخ انتهاء الوكالة يجب أن يكون بتنسيق السنة-الشهر-اليوم (Y-m-d).',
            'date_expiry.after_or_equal' => 'تاريخ انتهاء الوكالة يجب أن يكون بعد أو مساويًا لتاريخ الإصدار.',

            'file_attachment.file' => 'يجب أن يكون المرفق ملفًا صحيحًا.',
            'file_attachment.mimes' => 'يجب أن يكون المرفق من نوع JPG, JPEG, PNG, PDF, DOC, DOCX.',
            'file_attachment.max' => 'حجم المرفق يجب ألا يتجاوز 2 ميجابايت.',

            'notes.nullable' => 'حقل الملاحظات اختياري.',
        ];
    }
}
