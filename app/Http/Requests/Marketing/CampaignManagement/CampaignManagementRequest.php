<?php

namespace App\Http\Requests\Marketing\CampaignManagement;

use App\Enums\Marketing\CampaignManagement\CampaignStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CampaignManagementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'campaign_name'         => ['required', 'string', 'max:255'],
            'content_type_id'       => ['required', 'exists:settings_content_types,id'],
            'campaign_section_id'   => ['nullable', 'exists:settings_campaign_sections,id'],
            'content_purpose_id'    => ['required', 'exists:settings_content_purposes,id'],
            'social_id'             => ['required', 'exists:settings_socials,id'],
            'budget'                => ['nullable', 'numeric', 'min:0'],
            'start_date'            => ['required', 'date'],
            'end_date'              => ['required', 'date', 'after_or_equal:start_date'],
            'target_audience_id'    => ['nullable', 'exists:settings_target_audiences,id'],
            'text'                  => ['nullable', 'string'],
        ];
    }


    public function messages(): array
    {
        return [
            'required'              => 'حقل ":attribute" مطلوب.',
            'string'                => 'حقل ":attribute" يجب أن يكون نصًا.',
            'max'                   => 'حقل ":attribute" لا يجب أن يتجاوز :max حرفًا.',
            'exists'                => 'القيمة المحددة في حقل ":attribute" غير صالحة.',
            'numeric'               => 'حقل ":attribute" يجب أن يكون رقمًا.',
            'min'                   => 'حقل ":attribute" يجب أن لا يقل عن :min.',
            'date'                  => 'حقل ":attribute" يجب أن يكون تاريخًا صالحًا.',
            'after_or_equal'        => 'حقل ":attribute" يجب أن يكون بعد أو يساوي حقل "تاريخ البدء".',
            'in'                    => 'القيمة المحددة في حقل ":attribute" غير صالحة.',
        ];
    }


    public function attributes(): array
    {
        return [
            'campaign_name'         => 'اسم الحملة',
            'content_type_id'       => 'نوع الحملة',
            'campaign_section_id'   => 'قسم الحملة',
            'content_purpose_id'    => 'الهدف من الحملة',
            'social_id'             => 'المنصة الإعلانية',
            'budget'                => 'الميزانية الإجمالية',
            'start_date'            => 'تاريخ بدء الحملة',
            'end_date'              => 'تاريخ نهاية الحملة',
            'target_audience_id'    => 'الجمهور المستهدف',
            'text'                  => 'النص الإعلاني',
        ];
    }
}
