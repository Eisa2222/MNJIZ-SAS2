<?php

namespace App\Http\Requests\Marketing\CampaignManagement\CampaignResult;

use Illuminate\Foundation\Http\FormRequest;

class CampaignResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'report_date'       => ['required', 'date'],
            'spend'             => ['required', 'numeric', 'min:0'],
            'impressions'       => ['nullable', 'integer', 'min:0'],
            'clicks'            => ['nullable', 'integer', 'min:0'],
            'ctr'               => ['nullable', 'numeric', 'min:0'],
            'cpc'               => ['nullable', 'numeric', 'min:0'],
            'conversions'       => ['nullable', 'integer', 'min:0'],
            'conversion_value'  => ['nullable', 'numeric', 'min:0'],
            'roas'              => ['nullable', 'numeric', 'min:0'],
            'notes'             => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'حقل ":attribute" مطلوب.',
            'exists'   => 'القيمة المحددة في حقل ":attribute" غير صالحة.',
            'numeric'  => 'حقل ":attribute" يجب أن يكون رقمًا.',
            'integer'  => 'حقل ":attribute" يجب أن يكون عددًا صحيحًا.',
            'min'      => 'حقل ":attribute" يجب أن لا يقل عن :min.',
            'date'     => 'حقل ":attribute" يجب أن يكون تاريخًا صالحًا.',
        ];
    }

    public function attributes(): array
    {
        return [
            'report_date'       => 'تاريخ التقرير',
            'spend'             => 'الإنفاق الفعلي',
            'impressions'       => 'مرات الظهور',
            'clicks'            => 'عدد النقرات',
            'ctr'               => 'معدل النقر CTR',
            'cpc'               => 'تكلفة النقرة CPC',
            'conversions'       => 'عدد التحويلات',
            'conversion_value'  => 'قيمة التحويلات',
            'roas'              => 'العائد على الإنفاق ROAS',
            'notes'             => 'ملاحظات',
        ];
    }
}
