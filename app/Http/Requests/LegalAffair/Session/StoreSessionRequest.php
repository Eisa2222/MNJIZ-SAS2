<?php

namespace App\Http\Requests\LegalAffair\Session;

use Illuminate\Foundation\Http\FormRequest;

class StoreSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id'        => 'required|exists:projects,id',
            'lawsuit_id'        => 'required|exists:lawsuits,id',
            'assigned_to'       => 'required|array',
            'assigned_to.*'     => 'exists:users,id',
            'entity_ranks_id'   => 'required|exists:settings_entity_ranks,id',
            'session_date'      => 'required',
            'session_time'      => 'required|date_format:H:i',
        ];
    }

    public function messages(): array
    {
        return [
            // المشروع
            'project_id.required'           => 'المشروع مطلوب.',
            'project_id.exists'             => 'المشروع المحدد غير موجود.',

            // الدعوى
            'lawsuit_id.required'           => 'الدعوى مطلوبة.',
            'lawsuit_id.exists'             => 'الدعوى المحددة غير موجودة.',

            // المكلفين
            'assigned_to.required'          => 'يجب اختيار المكلفين.',
            'assigned_to.array'             => 'المكلفين يجب أن يكونوا في شكل قائمة.',
            'assigned_to.*.exists'          => 'أحد المكلفين المختارين غير موجود.',

            // درجة الجهة
            'entity_ranks_id.required'      => 'درجة الجهة مطلوبة.',
            'entity_ranks_id.exists'        => 'درجة الجهة المحددة غير موجودة.',

            // تاريخ الجلسة
            'session_date.required'         => 'تاريخ الجلسة مطلوب.',
            'session_date.date'             => 'تاريخ الجلسة يجب أن يكون تاريخ صحيح.',

            // وقت الجلسة
            'session_time.required'         => 'وقت الجلسة مطلوب.',
            'session_time.date_format'      => 'وقت الجلسة يجب أن يكون بصيغة ساعة:دقيقة (مثال: 14:30).',

        ];
    }

    public function attributes(): array
    {
        return [
            'project_id'        => 'المشروع',
            'lawsuit_id'        => 'الدعوى',
            'session_name'      => 'اسم الجلسة',
            'assigned_to'       => 'المكلفين',
            'entity_ranks_id'   => 'درجة الجهة',
            'session_date'      => 'تاريخ الجلسة',
            'session_time'      => 'وقت الجلسة',
        ];
    }
}
