<?php

namespace App\Http\Requests\LegalAffair\Opponent;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatOpponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(
            ['type' => ['required', Rule::in(['individual', 'company'])]],

            $this->commonRules(),

            $this->typeSpecificRules()
        );
    }

    protected function commonRules(): array
    {
        return [
            'name'                      => ['required', 'string', 'max:255'],
            'email'                     => [
                'nullable',
                'email',
                Rule::unique('opponents', 'email')->ignore($this->route('opponent')) // أو $this->opponent إذا كنت تستخدم Route Model Binding
            ],
            'contact_number'            => ['nullable', 'string', 'max:20'],
            'bio'                       => ['nullable', 'string'],
            'settings_region_id'        => ['nullable', 'exists:settings_regions,id'],
            'type'                      => ['required', 'in:individual,company'],
            'commercial_registration'   => ['nullable', 'string', 'max:100'],
            'unified_number'            => ['nullable', 'string', 'max:100'],
            'identity_number'           => ['nullable', 'string', 'max:100'],
        ];
    }

    protected function typeSpecificRules(): array
    {
        $type = $this->input('type');


        if ($type === 'company') {
            return [
                'authorizations'                    => ['nullable', 'array'],
                'authorizations.*.name'             => ['required_with:authorizations', 'string', 'max:255'],
                'authorizations.*.identity_number'        => ['required_with:authorizations', 'max:255'],
                'authorizations.*.phone'            => ['required_with:authorizations', 'string', 'max:15'],
                'authorizations.*.email'            => ['required_with:authorizations', 'email', 'max:255'],
            ];
        }

        return [];
    }

    public function messages(): array
    {
        return [
            // النوع
            'type.required'  => 'نوع الخصم مطلوب.',
            'type.in'        => 'نوع الخصم يجب أن يكون إما "فرد" أو "شخصية اعتبارية".',

            // الاسم
            'name.required'  => 'الاسم مطلوب.',
            'name.string'    => 'الاسم يجب أن يكون نصاً.',
            'name.max'       => 'الاسم لا يجب أن يتجاوز 255 حرفاً.',

            // البريد الإلكتروني
            'email.email'    => 'صيغة البريد الإلكتروني غير صحيحة.',
            'email.unique'   => 'هذا البريد الإلكتروني مستخدم مسبقًا.',

            // الهاتف
            'contact_number.string'   => 'رقم الهاتف يجب أن يكون نصاً.',
            'contact_number.max'      => 'رقم الهاتف لا يجب أن يتجاوز 20 حرفاً.',

            // النبذة
            'bio.string'     => 'النبذة يجب أن تكون نصاً.',

            // المنطقة
            'settings_region_id.exists' => 'المدينة المختارة غير موجودة.',

            // السجل التجاري
            'commercial_registration.string' => 'السجل التجاري يجب أن يكون نصاً.',
            'commercial_registration.max'    => 'السجل التجاري لا يجب أن يتجاوز 100 حرف.',

            // الرقم الموحد
            'unified_number.string' => 'الرقم الموحد يجب أن يكون نصاً.',
            'unified_number.max'    => 'الرقم الموحد لا يجب أن يتجاوز 100 حرف.',

            // رقم الهوية
            'identity_number.string' => 'رقم الهوية يجب أن يكون نصاً.',
            'identity_number.max'    => 'رقم الهوية لا يجب أن يتجاوز 100 حرف.',

            // المفوضين (في حالة الشخصية اعتبارية)
            'authorizations.array' => 'المفوضون يجب أن يكونوا ضمن مصفوفة.',
            'authorizations.*.name.required_with' => 'اسم المفوض مطلوب.',
            'authorizations.*.name.string'        => 'اسم المفوض يجب أن يكون نصاً.',
            'authorizations.*.name.max'           => 'اسم المفوض لا يجب أن يتجاوز 255 حرفاً.',

            'authorizations.*.identity_number.required_with' => 'رقم هوية المفوض مطلوب.',
            'authorizations.*.identity_number.max'           => 'رقم هوية المفوض لا يجب أن يتجاوز 255 حرفاً.',

            'authorizations.*.phone.required_with' => 'رقم هاتف المفوض مطلوب.',
            'authorizations.*.phone.string'        => 'رقم هاتف المفوض يجب أن يكون نصاً.',
            'authorizations.*.phone.max'           => 'رقم هاتف المفوض لا يجب أن يتجاوز 15 حرفاً.',

            'authorizations.*.email.required_with' => 'بريد المفوض الإلكتروني مطلوب.',
            'authorizations.*.email.email'         => 'صيغة بريد المفوض الإلكتروني غير صحيحة.',
            'authorizations.*.email.max'           => 'بريد المفوض لا يجب أن يتجاوز 255 حرفاً.',
        ];
    }
}
