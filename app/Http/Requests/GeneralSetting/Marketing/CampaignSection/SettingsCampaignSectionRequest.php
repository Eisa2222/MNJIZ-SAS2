<?php

namespace App\Http\Requests\GeneralSetting\Marketing\CampaignSection;

use Illuminate\Foundation\Http\FormRequest;

class SettingsCampaignSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'حقل الاسم مطلوب.',
            'name.string'    => 'يجب أن يكون الاسم نصاً.',
            'name.max'       => 'يجب ألا يتجاوز الاسم 255 حرفاً.',
            'color.required' => 'حقل اللون مطلوب.',
            'color.string'   => 'يجب أن يكون اللون نصاً.',
            'color.regex'    => 'صيغة اللون غير صحيحة. استخدم صيغة HEX مثل #AABBCC.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'  => 'الاسم',
            'color' => 'اللون',
        ];
    }
}
