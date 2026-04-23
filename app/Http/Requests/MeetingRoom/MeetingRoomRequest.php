<?php

namespace App\Http\Requests\MeetingRoom;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MeetingRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // توحيد الحقول المصفوفية (بدون استعلامات)
        $this->merge([
            'meeting_participants' => $this->input('meeting_participants', []),
            'attendees_employee'   => $this->input('attendees_employee', []),
            'attendees_customer'   => $this->input('attendees_customer', []),
            'additional_emails'    => $this->input('additional_emails', []),
        ]);
    }

    public function rules(): array
    {
        return [
            'title'     => ['required', 'string', 'min:3', 'max:100'],
            'hall'      => ['required', Rule::in(['big', 'small'])],
            'date'      => ['required', 'date', 'after_or_equal:today'],
            'from_time' => ['required', 'date_format:H:i'],
            'to_time'   => ['required', 'date_format:H:i', 'after:from_time'],
            'notes'     => ['nullable', 'string', 'max:500'],

            // الشكل العام فقط
            'meeting_participants'   => ['required', 'array', 'min:1'],
            'meeting_participants.*' => [Rule::in(['employees', 'customers', 'additional'])],

            'attendees_employee'   => ['nullable', 'array'],

            'attendees_customer'   => ['nullable', 'array'],

            'additional_emails'    => ['nullable', 'array'],
            'additional_emails.*'  => ['email'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'      => 'عنوان الاجتماع مطلوب.',
            'title.string'        => 'عنوان الاجتماع يجب أن يكون نصاً.',
            'title.min'           => 'عنوان الاجتماع يجب أن يكون على الأقل 3 أحرف.',
            'title.max'           => 'عنوان الاجتماع لا يمكن أن يتجاوز 100 حرف.',
            'hall.required'       => 'اختيار القاعة مطلوب.',
            'hall.in'             => 'القاعة المحددة غير صالحة.',
            'date.required'       => 'تاريخ الاجتماع مطلوب.',
            'date.date'           => 'تاريخ الاجتماع غير صالح.',
            'date.after_or_equal' => 'لا يمكن حجز اجتماع في الماضي.',
            'from_time.required'  => 'وقت بداية الاجتماع مطلوب.',
            'from_time.date_format' => 'صيغة وقت البداية غير صحيحة (HH:MM).',
            'to_time.required'    => 'وقت نهاية الاجتماع مطلوب.',
            'to_time.date_format' => 'صيغة وقت النهاية غير صحيحة (HH:MM).',
            'to_time.after'       => 'وقت النهاية يجب أن يكون بعد وقت البداية.',
            'notes.string'        => 'الملاحظات يجب أن تكون نصاً.',
            'notes.max'           => 'الملاحظات لا يمكن أن تتجاوز 500 حرف.',

            'meeting_participants.required' => 'يجب اختيار نوع أو أكثر من أطراف الاجتماع.',
            'meeting_participants.array'    => 'تنسيق أطراف الاجتماع غير صحيح.',
            'meeting_participants.*.in'     => 'نوع طرف الاجتماع غير صالح.',

            'additional_emails.*.email'     => 'الإيميل الإضافي غير صالح.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title'               => 'عنوان الاجتماع',
            'hall'                => 'القاعة',
            'date'                => 'التاريخ',
            'from_time'           => 'وقت البداية',
            'to_time'             => 'وقت النهاية',
            'notes'               => 'الملاحظات',
            'meeting_participants' => 'أطراف الاجتماع',
            'attendees_employee'  => 'الموظفون المدعوون',
            'attendees_customer'  => 'العملاء المدعوون',
            'additional_emails'   => 'بريد إضافي',
        ];
    }
}
