<?php

namespace App\Http\Requests\Marketing\ContentManagement;

use App\Enums\Marketing\ContentManagement\{
    ContentStatus,
    MediaType,
    PublicationStatus,
    PublishType,
    RecurringType
};
use App\Enums\Shared\WeekDay;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContentManagementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $mediaTypesNeedFile = implode(',', [
            MediaType::Image->value,
            MediaType::Video->value,
            MediaType::ImageText->value,
            MediaType::VideoText->value,
        ]);

        $mediaTypesNeedText = implode(',', [
            MediaType::Text->value,
            MediaType::ImageText->value,
            MediaType::VideoText->value,
        ]);

        return [

            'content_type_id'       => ['required', 'integer', 'exists:settings_content_types,id'],
            'publishing_pattern_id' => ['required', 'integer', 'exists:settings_publishing_patterns,id'],
            'content_purpose_id'    => ['required', 'integer', 'exists:settings_content_purposes,id'],


            'media_type'            => ['required', Rule::in(MediaType::values())],

            'media' => [
                Rule::requiredIf(fn() => in_array($this->input('media_type'), [
                    MediaType::Image->value,
                    MediaType::Video->value,
                    MediaType::ImageText->value,
                    MediaType::VideoText->value,
                ])),
                'nullable',
                'file',
                // 'max:10240',
                'mimetypes:image/jpeg,image/png,image/gif,video/mp4,video/quicktime,video/x-msvideo',
            ],

            // النص عند الحاجة
            'content_text'          => [
                Rule::requiredIf($this->filled('media_type') &&
                    in_array($this->media_type, explode(',', $mediaTypesNeedText))),
                'nullable',
                'string',
            ],

            /*--------------------------------------------------------------
            | المنصّات
            *-------------------------------------------------------------*/
            'socials'               => ['required', 'array', 'min:1'],
            'socials.*'             => ['integer', 'exists:settings_socials,id'],

            /*--------------------------------------------------------------
            | حالة المحتوى
            *-------------------------------------------------------------*/
            'publication_status'                => ['required', Rule::in(PublicationStatus::values())],
            'publication_date'      => ['required_if:publication_status,published', 'nullable', 'date'],

            /*--------------------------------------------------------------
            | جدولة
            *-------------------------------------------------------------*/
            'publish_type'          => ['required_if:publication_status,scheduled', 'nullable', Rule::in(PublishType::values())],
            'one_time_at'           => ['required_if:publish_type,one_time',   'nullable', 'date'],

            'recurring_type'        => ['required_if:publish_type,recurring', 'nullable', Rule::in(RecurringType::values())],
            'publish_time'          => ['required_if:publish_type,recurring', 'nullable', 'date_format:H:i'],
            'start_date'            => ['required_if:publish_type,recurring', 'nullable', 'date'],
            'end_date'              => ['required_if:publish_type,recurring', 'nullable', 'date', 'after_or_equal:start_date'],
            'is_active'             => ['nullable', 'boolean'],

            'month_day'             => ['required_if:recurring_type,monthly', 'nullable', 'integer', 'min:1', 'max:31'],

            'week_days'             => ['required_if:recurring_type,weekly',  'nullable', 'array', 'min:1'],
            'week_days.*'           => ['integer', Rule::in(WeekDay::values())],
        ];
    }

    public function messages(): array
    {
        return [
            'required'          => 'حقل ":attribute" مطلوب.',
            'required_if'       => 'حقل ":attribute" مطلوب عندما تكون قيمة ":other" هي ":value".',
            'integer'           => 'حقل ":attribute" يجب أن يكون عددًا صحيحًا.',
            'exists'            => 'القيمة المختارة فى ":attribute" غير صحيحة.',
            'in'                => 'القيمة فى ":attribute" غير صحيحة.',
            'url'               => 'حقل ":attribute" يجب أن يكون رابطًا صحيحًا.',
            'date'              => 'حقل ":attribute" يجب أن يكون تاريخًا صحيحًا.',
            'date_format'       => 'حقل ":attribute" يجب أن يكون بالتنسيق HH:MM.',
            'after_or_equal'    => 'حقل ":attribute" يجب أن يكون بعد أو يساوى تاريخ البدء.',
            'file'              => 'حقل ":attribute" يجب أن يكون ملفًا صالحًا.',
            'mimes'             => 'نوع الملف فى ":attribute" غير مدعوم.',
            'max'               => 'حقل ":attribute" لا يجب أن يتجاوز :max كيلوبايت.',
            'array'             => 'حقل ":attribute" يجب أن يكون مصفوفة.',
            'min'               => 'حقل ":attribute" يجب أن يحتوى على عنصر واحد على الأقل.',
        ];
    }

    public function attributes(): array
    {
        return [
            'content_type_id'       => 'نوع المحتوى',
            'publishing_pattern_id' => 'نمط النشر',
            'content_purpose_id'    => 'هدف المحتوى',

            'media_type'            => 'نوع الوسائط',
            'media'                 => ' الوسائط',
            'content_text'          => 'نص المحتوى',

            'socials'               => 'المنصات',

            'publication_status'    => 'حالة النشر ',
            'publication_date'      => 'تاريخ النشر',
            'publish_type'          => 'نوع الجدولة',
            'one_time_at'           => 'تاريخ ووقت النشر لمرة واحدة',
            'recurring_type'        => 'نمط التكرار',
            'publish_time'          => 'وقت النشر',
            'start_date'            => 'تاريخ البدء',
            'end_date'              => 'تاريخ الانتهاء',
            'month_day'             => 'يوم الشهر',
            'week_days'             => 'أيام النشر',
        ];
    }

    /** لتحويل مفتاح is_active إلى Boolean حقيقى */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
