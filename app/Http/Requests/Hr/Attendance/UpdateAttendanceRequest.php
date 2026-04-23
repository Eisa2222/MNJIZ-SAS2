<?php

namespace App\Http\Requests\Hr\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
{

    /*
    |--------------------------------------------------------------------------
    | Determine if the user is authorized to make this request.
    |--------------------------------------------------------------------------
    */
    public function authorize(): bool
    {
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Get the validation rules that apply to the request.
    |--------------------------------------------------------------------------
    */
    public function rules(): array
    {
        return [
            'check_in_time'  => 'nullable|required_if:day_status,present|date_format:H:i',
            'check_out_time' => 'nullable|required_if:day_status,present|date_format:H:i|after:check_in_time',
            'day_status'     => 'required|in:present,absent,leave',
            'edit_reason'    => 'required|string|max:255',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Get custom error messages for validator errors.
    |--------------------------------------------------------------------------
    */
    public function messages(): array
    {
        return [
            'check_in_time.required_if'  => 'وقت الدخول مطلوب في حالة الحضور',
            'check_out_time.required_if' => 'وقت الخروج مطلوب في حالة الحضور',
            'check_in_time.date_format'  => 'الصيغة يجب أن تكون ساعة:دقيقة مثل 08:30',
            'check_out_time.date_format' => 'الصيغة يجب أن تكون ساعة:دقيقة مثل 16:00',
            'check_out_time.after'       => 'وقت الخروج يجب أن يكون بعد وقت الدخول',
        ];
    }
}
