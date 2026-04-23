<?php

namespace App\Http\Requests\GeneralSetting\SystemSetting;


use Illuminate\Foundation\Http\FormRequest;

class companyAttachmentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; //
    }

    public function rules(): array
    {
        return [
            // السجل التجاري
            'commercial_register'           => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'commercial_register_end_date'  => ['nullable', 'date', 'after:today', 'required_with:commercial_register'],
            // التأمينات
            'insurance'                     => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'insurance_end_date'            => ['nullable', 'date', 'after:today', 'required_with:insurance'],
            // الغرفة التجارية
            'chamber_of_commerce'           => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'chamber_end_date'              => ['nullable', 'date', 'after:today', 'required_with:chamber_of_commerce'],
            // بلدي
            'balady'                        => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'balady_end_date'               => ['nullable', 'date', 'after:today', 'required_with:balady'],
            // التوطين
            'tawteen'                       => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'tawteen_end_date'              => ['nullable', 'date', 'after:today', 'required_with:tawteen'],
            // حماية الأجور
            'wage_protection'               => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'wage_protection_end_date'      => ['nullable', 'date', 'after:today', 'required_with:wage_protection'],
            // عقد التأسيس
            'incorporation_contract'        => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            // العنوان الوطني
            'national_address'              => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            // رسائل الملفات
            '*.file'            => 'يجب أن يكون :attribute ملفًا صالحًا.',
            '*.mimes'           => 'يجب أن يكون :attribute من نوع: PDF, JPG, JPEG, PNG.',
            '*.max'             => 'يجب أن لا يزيد حجم :attribute عن 5 ميجابايت.',

            // رسائل التواريخ
            '*.date'            => 'يجب أن يكون :attribute تاريخًا صالحًا.',
            '*.after'           => 'يجب أن يكون :attribute تاريخًا في المستقبل.',
            '*.required_with'   => 'حقل :attribute مطلوب عند رفع الملف.',

            // رسائل مخصصة لكل حقل
            'commercial_register.file'                      => 'يجب أن يكون ملف السجل التجاري ملفًا صالحًا.',
            'commercial_register.mimes'                     => 'يجب أن يكون ملف السجل التجاري من نوع: PDF, JPG, JPEG, PNG.',
            'commercial_register.max'                       => 'يجب أن لا يزيد حجم ملف السجل التجاري عن 5 ميجابايت.',
            'commercial_register_end_date.after'            => 'يجب أن يكون تاريخ انتهاء السجل التجاري في المستقبل.',
            'commercial_register_end_date.required_with'    => 'تاريخ انتهاء السجل التجاري مطلوب عند رفع الملف.',

            'insurance.file'                                => 'يجب أن يكون ملف التأمينات ملفًا صالحًا.',
            'insurance.mimes'                               => 'يجب أن يكون ملف التأمينات من نوع: PDF, JPG, JPEG, PNG.',
            'insurance.max'                                 => 'يجب أن لا يزيد حجم ملف التأمينات عن 5 ميجابايت.',
            'insurance_end_date.after'                      => 'يجب أن يكون تاريخ انتهاء التأمينات في المستقبل.',
            'insurance_end_date.required_with'              => 'تاريخ انتهاء التأمينات مطلوب عند رفع الملف.',

            'chamber_of_commerce.file'                      => 'يجب أن يكون ملف الغرفة التجارية ملفًا صالحًا.',
            'chamber_of_commerce.mimes'                     => 'يجب أن يكون ملف الغرفة التجارية من نوع: PDF, JPG, JPEG, PNG.',
            'chamber_of_commerce.max'                       => 'يجب أن لا يزيد حجم ملف الغرفة التجارية عن 5 ميجابايت.',
            'chamber_end_date.after'                        => 'يجب أن يكون تاريخ انتهاء الغرفة التجارية في المستقبل.',
            'chamber_end_date.required_with'                => 'تاريخ انتهاء الغرفة التجارية مطلوب عند رفع الملف.',

            'balady.file'                                   => 'يجب أن يكون ملف بلدي ملفًا صالحًا.',
            'balady.mimes'                                  => 'يجب أن يكون ملف بلدي من نوع: PDF, JPG, JPEG, PNG.',
            'balady.max'                                    => 'يجب أن لا يزيد حجم ملف بلدي عن 5 ميجابايت.',
            'balady_end_date.after'                         => 'يجب أن يكون تاريخ انتهاء بلدي في المستقبل.',
            'balady_end_date.required_with'                 => 'تاريخ انتهاء بلدي مطلوب عند رفع الملف.',

            'tawteen.file'                                  => 'يجب أن يكون ملف التوطين ملفًا صالحًا.',
            'tawteen.mimes'                                 => 'يجب أن يكون ملف التوطين من نوع: PDF, JPG, JPEG, PNG.',
            'tawteen.max'                                   => 'يجب أن لا يزيد حجم ملف التوطين عن 5 ميجابايت.',
            'tawteen_end_date.after'                        => 'يجب أن يكون تاريخ انتهاء التوطين في المستقبل.',
            'tawteen_end_date.required_with'                => 'تاريخ انتهاء التوطين مطلوب عند رفع الملف.',

            'wage_protection.file'                          => 'يجب أن يكون ملف حماية الأجور ملفًا صالحًا.',
            'wage_protection.mimes'                         => 'يجب أن يكون ملف حماية الأجور من نوع: PDF, JPG, JPEG, PNG.',
            'wage_protection.max'                           => 'يجب أن لا يزيد حجم ملف حماية الأجور عن 5 ميجابايت.',
            'wage_protection_end_date.after'                => 'يجب أن يكون تاريخ انتهاء حماية الأجور في المستقبل.',
            'wage_protection_end_date.required_with'        => 'تاريخ انتهاء حماية الأجور مطلوب عند رفع الملف.',

            'incorporation_contract.file'                   => 'يجب أن يكون ملف عقد التأسيس ملفًا صالحًا.',
            'incorporation_contract.mimes'                  => 'يجب أن يكون ملف عقد التأسيس من نوع: PDF, JPG, JPEG, PNG.',
            'incorporation_contract.max'                    => 'يجب أن لا يزيد حجم ملف عقد التأسيس عن 5 ميجابايت.',

            'national_address.file'                         => 'يجب أن يكون ملف العنوان الوطني ملفًا صالحًا.',
            'national_address.mimes'                        => 'يجب أن يكون ملف العنوان الوطني من نوع: PDF, JPG, JPEG, PNG.',
            'national_address.max'                          => 'يجب أن لا يزيد حجم ملف العنوان الوطني عن 5 ميجابايت.',
        ];
    }

    public function attributes(): array
    {
        return [
            'commercial_register'                   => 'ملف السجل التجاري',
            'commercial_register_end_date'          => 'تاريخ انتهاء السجل التجاري',
            'insurance'                             => 'ملف التأمينات',
            'insurance_end_date'                    => 'تاريخ انتهاء التأمينات',
            'chamber_of_commerce'                   => 'ملف الغرفة التجارية',
            'chamber_end_date'                      => 'تاريخ انتهاء الغرفة التجارية',
            'balady'                                => 'ملف بلدي',
            'balady_end_date'                       => 'تاريخ انتهاء بلدي',
            'tawteen'                               => 'ملف التوطين',
            'tawteen_end_date'                      => 'تاريخ انتهاء التوطين',
            'wage_protection'                       => 'ملف حماية الأجور',
            'wage_protection_end_date'              => 'تاريخ انتهاء حماية الأجور',
            'incorporation_contract'                => 'ملف عقد التأسيس',
            'national_address'                      => 'ملف العنوان الوطني',
        ];
    }
}
