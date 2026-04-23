<?php

namespace App\Http\Requests\LegalAffair\Lawsuit;



use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLawsuitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                              => 'required|string|max:255',
            'lawsuit_number'                    => [
                'required',
                Rule::unique('lawsuits', 'lawsuit_number')->ignore($this->route('lawsuit')),
                'regex:/^\d+$/'
            ],
            'project_id'                        => 'required|exists:projects,id',
            'main_courts_id'                    => 'required|exists:settings_main_courts,id',
            'regions_id'                        => 'required|exists:settings_regions,id',
            'circle'                            => 'required|nullable|string',
            'category_id'                       => 'required|exists:settings_categories,id',
            'subcategory_id'                    => 'required|exists:settings_subcategories,id',
            'lawsuit_type_id'                   => 'required|exists:settings_lawsuits_types,id',
            'lawsuit_subject'                   => 'nullable',
            'plaintiff_requests'                => 'nullable',
            'lawsuit_proofs'                    => 'nullable',

            'plaintiff_id'                      => 'required|array',
            'plaintiff_id.*'                    => 'required|string',
            'defendant_id'                      => 'required|array',
            'defendant_id.*'                    => 'required|string',

            'assigned_to'                       => 'array',
        ];
    }



    public function messages(): array
    {
        return [
            'name.required'                     => 'يرجى إدخال اسم الدعوى.',
            'name.string'                       => 'اسم الدعوى يجب أن يكون نصياً.',
            'name.max'                          => 'اسم الدعوى لا يجب أن يتجاوز 255 حرفاً.',

            'lawsuit_number.required'           => 'يرجى إدخال رقم الدعوى.',
            'lawyer_number.regex'               => 'حقل رقم الدعوى يجب أن يحتوي على أرقام فقط.',
            'lawsuit_number.unique'             => 'رقم الدعوى مُسجل مسبقاً.',


            'project_id.required'                   => 'يرجى اختيار المشروع.',
            'project_id.exists'                     => 'المشروع المحدد غير موجود.',

            'main_courts_id.required'               => 'يرجى اختيار المحكمة.',
            'main_courts_id.exists'                 => 'المحكمة المحددة غير موجودة.',

            'regions_id.required'                   => 'يرجى اختيار المدينة.',
            'regions_id.exists'                     => 'المدينة المحددة غير موجودة.',

            'circle.required'                       => 'يرجى إدخال الدائرة.',


            'category_id.required'                  => 'يرجى اختيار الفئة.',
            'category_id.exists'                    => 'الفئة المحددة غير موجودة.',

            'subcategory_id.required'               => 'يرجى اختيار الفئة الفرعية.',
            'subcategory_id.exists'                 => 'الفئة الفرعية المحددة غير موجودة.',

            'lawsuit_type_id.required'              => 'يرجى اختيار نوع الدعوى.',
            'lawsuit_type_id.exists'                => 'نوع الدعوى المحدد غير موجود.',

            'plaintiff_id.required'                 => 'يرجى اختيار المدعين.',
            'plaintiff_id.array'                    => 'المدعين يجب أن يكونوا في شكل قائمة.',
            'plaintiff_id.*.required'               => 'كل مدعي يجب أن يتم تحديده.',
            'plaintiff_id.*.string'                 => 'اسم المدعي يجب أن يكون نصياً.',

            'defendant_id.required'                 => 'يرجى اختيار المدعى عليهم.',
            'defendant_id.array'                    => 'المدعى عليهم يجب أن يكونوا في شكل قائمة.',
            'defendant_id.*.required'               => 'كل مدعى عليه يجب أن يتم تحديده.',
            'defendant_id.*.string'                 => 'اسم المدعى عليه يجب أن يكون نصياً.',

            'assigned_to.required'                  => 'يجب اختيار على الأقل مكلف واحد.',
            'assigned_to.*.exists'                  => 'أحد الموظفين المختارين غير موجود.',
        ];
    }
}
