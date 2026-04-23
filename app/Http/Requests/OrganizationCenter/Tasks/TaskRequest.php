<?php

namespace App\Http\Requests\OrganizationCenter\Tasks;

use App\Enums\OrganizationCenter\Tasks\Task\TaskField;
use App\Enums\OrganizationCenter\Tasks\Task\TaskPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'task_name'                     => ['required', 'string', 'max:255'],
            'priority'                      => ['required', Rule::in(TaskPriority::values())],
            'task_field'                    => ['required', Rule::in(TaskField::values())],

            'description'                   => ['nullable', 'string'],

            'offer_id'                      => ['nullable', 'integer', 'exists:offers,id'],
            'contract_id'                   => ['nullable', 'integer', 'exists:contracts,id'],
            'project_id'                    => ['nullable', 'integer', 'exists:projects,id'],
            'lawsuit_id'                    => ['nullable', 'integer', 'exists:lawsuits,id'],
            'session_id'                    => ['nullable', 'integer', 'exists:sessions,id'],
            'power_of_attorney_id'          => ['nullable', 'integer', 'exists:power_of_attorneys,id'],

            'marketing_id'                  => ['nullable', 'integer', 'exists:settings_marketing_channels,id'],
            'detailed_marketing_channel_id' => ['nullable', 'integer', 'exists:employees,id'],
            'customer_id'                   => ['nullable', 'integer', 'exists:customers,id'],
            'social_media_id'               => ['nullable', 'integer', 'exists:settings_socials,id'],

            'due_date'                      => ['required', 'date'],
            'task_start_date'               => ['nullable', 'date'],
            'task_end_date'                 => ['nullable', 'date', 'after_or_equal:task_start_date'],

            'assigned_user_ids'             => ['required', 'array', 'min:1'],
            'assigned_user_ids.*'           => ['exists:users,id'],

            'steps'                         => ['nullable', 'array', 'min:1'],
            'steps.*.name'                  => ['required', 'string', 'min:3', 'max:100'],
            'steps.*.needs_approval'        => ['sometimes', 'boolean'],
            'steps.*.assigned_user_ids'     => ['required', 'array', 'min:1'],
            'steps.*.assigned_user_ids.*'   => ['exists:users,id'],

            'attachments'                   => ['sometimes', 'array'],
            'attachments.*'                 => [
                'file',
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,zip,rar,txt',
                'max:10240',
            ],
        ];
    }


    public function messages(): array
    {
        return [
            'task_name.required'                => 'عنوان المهمة مطلوب.',
            'priority.required'                 => 'أولوية المهمة مطلوبة.',
            'task_field.required'               => 'مجال المهمة مطلوب.',
            'assigned_user_ids.required'        => 'يجب اختيار مكلف واحد على الأقل للمهمة.',

            'steps.required'                    => 'أضف خطوة واحدة على الأقل.',
            'steps.*.name.required'             => 'اسم الخطوة مطلوب.',
            'steps.*.assigned_user_ids.required' => 'اختر مكلفًا واحدًا على الأقل لهذه الخطوة.',

            'attachments.*.mimes'               => 'صيغة المرفق غير مدعومة.',
            'attachments.*.max'                 => 'الحجم الأقصى لكل ملف هو 10 ميجابايت.',
        ];
    }
}
