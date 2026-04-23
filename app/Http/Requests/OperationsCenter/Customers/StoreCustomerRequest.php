<?php

namespace App\Http\Requests\OperationsCenter\Customers;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(
            // تحقّق نوع العميل أولاً
            ['customer_type' => ['required', Rule::in(['individual', 'company'])]],

            // القواعد المشتركة
            $this->commonRules(),

            // القواعد الخاصة بنوع العميل
            $this->typeSpecificRules()
        );
    }

    protected function commonRules(): array
    {
        return [
            'name'                          => ['required', 'string', 'max:255'],
            'contact_number'                => ['nullable', 'string', 'max:15', 'unique:customers,contact_number'],
            'email'                         => ['nullable', 'email', 'max:255'],
            'address'                       => ['nullable', 'string', 'max:255'],
            'relationship_manager_id'       => ['required', 'integer', 'exists:employees,id'],
            'department_id'                   => ['required', 'integer', 'exists:settings_department_contract_cases,id'],
            'marketing_channel_id'          => ['nullable', 'integer', 'exists:settings_marketing_channels,id'],
            'detailed_marketing_channel_id' => ['nullable', 'integer', 'exists:employees,id'],
            'parent_customer_id'            => ['nullable', 'integer', 'exists:customers,id'],
            'social_media_id'               => ['nullable', 'integer', 'exists:settings_socials,id'],
        ];
    }

    protected function typeSpecificRules(): array
    {
        $type = $this->input('customer_type');

        if ($type === 'individual') {
            return [
                'title'            => ['nullable', 'string', 'max:255'],
                'nationality_id'      => ['nullable', 'integer', 'exists:settings_countries,id'],
                'status_id'           => ['required', 'integer', 'exists:settings_client_statuses,id'],
                'civil_registry_number'   => ['required', 'string'],
            ];
        }

        if ($type === 'company') {
            return [
                'commercial_registration_number'    => ['nullable', 'regex:/^[0-9]+$/'],
                'unified_number'                    => ['nullable', 'regex:/^[0-9]+$/'],
                'sector_id'                         => ['nullable', 'integer', 'exists:settings_sectors,id'],
                'authorizations'                    => ['nullable', 'array'],
                'authorizations.*.name'             => ['required_with:authorizations', 'string', 'max:255'],
                'authorizations.*.id_number'        => ['required_with:authorizations', 'max:255'],
                'authorizations.*.phone'            => ['required_with:authorizations', 'string', 'max:15'],
                'authorizations.*.email'            => ['required_with:authorizations', 'email', 'max:255'],
            ];
        }

        return [];
    }

    public function messages(): array
    {
        $messages = [
            // مشتركة
            'name.required'                       => 'حقل الاسم مطلوب.',
            'name.string'                         => 'حقل الاسم يجب أن يكون نصًا.',
            'name.max'                            => 'حقل الاسم لا يجب أن يزيد عن 255 حرفًا.',

            'contact_number.string'                => 'رقم الاتصال يجب أن يكون نصًا.',
            'contact_number.max'                   => 'رقم الاتصال لا يجب أن يتجاوز 15 حرفًا.',
            'contact_number.unique'                => 'رقم الاتصال مُستخدم بالفعل.',

            'email.email'                         => 'يرجى إدخال عنوان بريد إلكتروني صالح.',
            'email.max'                           => 'لا يجوز أن يتجاوز البريد الإلكتروني 255 حرفًا.',

            'address.string'                      => 'حقل العنوان يجب أن يكون نصًا.',
            'address.max'                         => 'حقل العنوان لا يجب أن يزيد عن 255 حرفًا.',

            'relationship_manager_id.required'        => 'يرجى اختيار مسؤول العلاقة.',
            'relationship_manager_id.integer'         => 'مسؤول العلاقة يجب أن يكون من القائمة.',
            'relationship_manager_id.exists'          => 'مسؤول العلاقة المحدد غير موجود.',

            'department_id.required'                  => 'يرجى اختيار قسم العميل .',
            'department_id.integer'                   => 'قسم العميل يجب أن يكون من القائمة.',
            'department_id.exists'                    => 'قسم العميل المحدد غير موجود.',

            'marketing_channel_id.integer'            => 'حقل قناة التسويق يجب أن يكون من القائمة.',
            'marketing_channel_id.exists'             => 'قناة التسويق المحددة غير موجودة.',

            'detailed_marketing_channel_id.integer'    => 'حقل قناة التسويق التفصيلية يجب أن يكون من القائمة.',
            'detailed_marketing_channel_id.exists'     => 'قناة التسويق التفصيلية المحددة غير موجودة.',

            'parent_customer_id.integer'                  => 'العميل يجب أن يكون من القائمة.',
            'parent_customer_id.exists'                   => 'العميل المحدد غير موجود.',

            'social_media_id.integer'             => 'وسائل التواصل الاجتماعي يجب أن تكون من القائمة.',
            'social_media_id.exists'              => 'وسائل التواصل الاجتماعي المحددة غير موجودة.',

            'customer_type.required'              => 'نوع العميل مطلوب.',
            'customer_type.in'                    => 'نوع العميل غير صالح.',
        ];

        // رسائل لفردي
        if ($this->input('customer_type') === 'individual') {
            $messages = array_merge($messages, [
                'title.string'             => 'حقل الكنية يجب أن يكون نصًا.',
                'title.max'                => 'حقل الكنية لا يجب أن يزيد عن 255 حرفًا.',

                'nationality_id.integer'      => 'حقل الجنسية يجب أن يكون من القائمة.',
                'nationality_id.exists'       => 'الجنسية المحددة غير موجودة.',

                'status_id.required'          => 'حقل حالة العميل مطلوب.',
                'status_id.exists'            => 'حالة العميل المحددة غير موجودة.',

                'civil_registry_number.required'  => 'حقل السجل المدني مطلوب.',
            ]);
        }

        // رسائل لشركة
        if ($this->input('customer_type') === 'company') {
            $messages = array_merge($messages, [
                'commercial_registration_number.regex' => 'رقم السجل التجاري يجب أن يحتوي على أرقام فقط.',
                'unified_number.regex'                 => 'الرقم الموحد يجب أن يحتوي على أرقام فقط.',

                'sector_id.integer'                       => 'حقل القطاع يجب أن يكون من القائمة.',
                'sector_id.exists'                        => 'القطاع المحدد غير موجود.',

                'authorizations.array'                 => 'حقل التفويضات يجب أن يكون مصفوفة.',

                'authorizations.*.name.required_with'       => 'اسم المفوض مطلوب.',
                'authorizations.*.id_number.required_with'  => 'رقم هوية المفوض مطلوب.',
                'authorizations.*.phone.required_with'      => 'رقم هاتف المفوض مطلوب.',
                'authorizations.*.email.email'              => 'يرجى إدخال بريد إلكتروني صالح للمفوض.',
            ]);
        }

        return $messages;
    }
}
